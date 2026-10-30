<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ImportStudentRoster extends Command
{
    protected $signature = 'roster:import {file? : CSV file with lrn and section columns}';

    protected $description = 'Import an official student LRN roster without importing other workbook data';

    public function handle(): int
    {
        $path = $this->argument('file') ?: storage_path('app/private/grade7_roster.csv');
        if (!is_file($path) || !is_readable($path)) {
            $this->error("Roster CSV is missing or unreadable: {$path}");
            return self::FAILURE;
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->error('Could not open the roster CSV.');
            return self::FAILURE;
        }

        try {
            $header = fgetcsv($handle);
            if ($header === false) {
                throw new RuntimeException('The roster CSV is empty.');
            }

            $header = array_map(static fn ($column) => strtolower(trim((string) $column)), $header);
            $lrnColumn = array_search('lrn', $header, true);
            $sectionColumn = array_search('section', $header, true);
            if ($lrnColumn === false || $sectionColumn === false) {
                throw new RuntimeException('The roster CSV must include lrn and section columns.');
            }

            $sections = DB::table('sections')->pluck('id', 'name');
            $sectionIds = [];
            foreach ($sections as $name => $id) {
                $sectionIds[strtolower(trim($name))] = (int) $id;
            }

            $rows = [];
            $invalidRows = 0;
            $duplicateRows = 0;
            $line = 1;
            while (($data = fgetcsv($handle)) !== false) {
                $line++;
                $lrn = trim((string) ($data[$lrnColumn] ?? ''));
                $sectionName = trim((string) ($data[$sectionColumn] ?? ''));
                if ($lrn === '' && $sectionName === '') {
                    continue;
                }
                if (!preg_match('/^[0-9]{12}$/', $lrn) || !isset($sectionIds[strtolower($sectionName)])) {
                    $invalidRows++;
                    continue;
                }

                $sectionId = $sectionIds[strtolower($sectionName)];
                if (isset($rows[$lrn])) {
                    if ($rows[$lrn] !== $sectionId) {
                        throw new RuntimeException("Duplicate LRN is assigned to different sections (CSV line {$line}).");
                    }
                    $duplicateRows++;
                    continue;
                }
                $rows[$lrn] = $sectionId;
            }

            DB::transaction(function () use ($rows, $sections): void {
                foreach ($rows as $lrn => $sectionId) {
                    DB::table('student_rosters')->updateOrInsert(
                        ['lrn' => $lrn],
                        ['section_id' => $sectionId, 'updated_at' => now(), 'created_at' => now()]
                    );

                    $sectionName = $sections->search($sectionId);
                    if ($sectionName === false) {
                        throw new RuntimeException('An imported roster section could not be resolved.');
                    }

                    $studentIds = DB::table('users')
                        ->where('role', 'student')
                        ->where('lrn', $lrn)
                        ->pluck('id');

                    DB::table('users')
                        ->whereIn('id', $studentIds)
                        ->update([
                            'section' => $sectionName,
                            'is_verified' => true,
                            'updated_at' => now(),
                        ]);
                }
            });
        } catch (Throwable $exception) {
            $this->error('Roster import failed: ' . $exception->getMessage());
            return self::FAILURE;
        } finally {
            fclose($handle);
        }

        $this->info(sprintf(
            'Imported %d unique roster entries. Skipped %d duplicate and %d invalid rows.',
            count($rows),
            $duplicateRows,
            $invalidRows
        ));

        return self::SUCCESS;
    }
}
