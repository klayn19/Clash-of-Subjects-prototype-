<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GeneralKnowledgeQuarterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('subject');
            $table->unsignedTinyInteger('quarter');
            $table->string('type');
            $table->string('difficulty')->nullable();
            $table->text('question');
            $table->string('choice_a');
            $table->string('choice_b');
            $table->string('choice_c');
            $table->string('choice_d');
            $table->string('answer');
            $table->text('explanation')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('questions');

        parent::tearDown();
    }

    public function test_questions_are_filtered_by_subject_and_quarter(): void
    {
        $quarterOneId = $this->createQuestion('math', 1);
        $quarterTwoId = $this->createQuestion('math', 2);
        $otherSubjectId = $this->createQuestion('science', 2);

        $this->getJson('/api/general-knowledge/questions?subject=math&quarter=2')
            ->assertOk()
            ->assertJsonPath('subject', 'math')
            ->assertJsonPath('quarter', 2)
            ->assertJsonCount(1, 'questions')
            ->assertJsonPath('questions.0.id', $quarterTwoId)
            ->assertJsonMissing(['id' => $quarterOneId])
            ->assertJsonMissing(['id' => $otherSubjectId]);
    }

    public function test_quarter_is_required_and_must_be_between_one_and_four(): void
    {
        $this->getJson('/api/general-knowledge/questions?subject=math')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quarter');

        $this->getJson('/api/general-knowledge/questions?subject=math&quarter=5')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quarter');
    }

    public function test_question_answers_are_returned_as_uppercase_letters(): void
    {
        $questionId = $this->createQuestion('math', 1);
        DB::table('questions')->where('id', $questionId)->update(['answer' => ' a ']);

        $this->getJson('/api/general-knowledge/questions?subject=math&quarter=1')
            ->assertOk()
            ->assertJsonPath('questions.0.answer', 'A');
    }

    public function test_answer_text_matching_choice_is_returned_as_its_letter(): void
    {
        $questionId = $this->createQuestion('math', 1);
        DB::table('questions')->where('id', $questionId)->update([
            'choice_a' => 'Headings and subheadings',
            'answer' => 'HEADINGS AND SUBHEADINGS',
        ]);

        $this->getJson('/api/general-knowledge/questions?subject=math&quarter=1')
            ->assertOk()
            ->assertJsonPath('questions.0.answer', 'A');

        $this->getJson('/api/generate_ai_question?subject=math')
            ->assertOk()
            ->assertJsonPath('answer', 'A');

        $this->getJson('/api/get_question?subject=math')
            ->assertOk()
            ->assertJsonPath('answer', 'A');
    }

    public function test_student_can_see_quarter_selection_after_subject_selection(): void
    {
        $this->withSession(['user_role' => 'student'])
            ->get('/student/general-knowledge')
            ->assertOk()
            ->assertSee('stage-quarters')
            ->assertSee('CHOOSE A QUARTER')
            ->assertSee('selectSubject')
            ->assertSee('startArena(4)');
    }

    public function test_single_player_question_endpoint_filters_by_optional_quarter(): void
    {
        $matchingId = $this->createQuestion('math', 3, 'average');
        $this->createQuestion('math', 2, 'average');
        $this->createQuestion('science', 3, 'average');

        $this->getJson('/api/generate_ai_question?subject=math&quarter=3&difficulty=5')
            ->assertOk()
            ->assertJsonPath('id', $matchingId)
            ->assertJsonPath('subject', 'math');
    }

    public function test_single_player_question_endpoint_rejects_invalid_quarter(): void
    {
        $this->getJson('/api/generate_ai_question?subject=math&quarter=5')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quarter');
    }

    public function test_exam_question_lookup_keeps_the_selected_subject_type_and_quarter(): void
    {
        $matchingId = $this->createQuestion('english', 1, null, 'exam');
        $this->createQuestion('math', 1, null, 'exam');
        $this->createQuestion('science', 1, null, 'exam');
        $this->createQuestion('english', 2, null, 'exam');
        $this->createQuestion('english', 1, null, 'quiz');

        $this->getJson('/api/get_question?subject=English&type=exam&quarter=1')
            ->assertOk()
            ->assertJsonPath('id', $matchingId);
    }

    public function test_exam_question_lookup_does_not_fall_back_to_a_different_subject_or_quarter(): void
    {
        $this->createQuestion('math', 1, null, 'exam');
        $this->createQuestion('science', 1, null, 'exam');
        $this->createQuestion('english', 2, null, 'exam');
        $this->createQuestion('english', 1, null, 'quiz');

        $this->getJson('/api/get_question?subject=english&type=exam&quarter=1')
            ->assertOk()
            ->assertJsonPath('completed', true);
    }

    public function test_gameplay_cycles_through_all_quiz_questions_for_the_class_and_shared_pool(): void
    {
        $classQuestionId = $this->createQuestion('math', 1, null, 'quiz', 7);
        $sharedQuestionId = $this->createQuestion('math', 1);
        $this->createQuestion('math', 1, null, 'quiz', 8);
        $this->createQuestion('math', 1, null, 'exam', 7);

        $firstQuestion = $this->getJson('/api/get_question?class_id=7&subject=math&quarter=1')
            ->assertOk()
            ->assertJsonPath('total_questions', 2)
            ->json('id');

        $secondQuestion = $this->getJson(
            '/api/get_question?class_id=7&subject=math&quarter=1&answered=' . $firstQuestion
        )
            ->assertOk()
            ->assertJsonPath('total_questions', 2)
            ->assertJsonPath('is_last', true)
            ->json('id');

        $this->assertEqualsCanonicalizing(
            [$classQuestionId, $sharedQuestionId],
            [$firstQuestion, $secondQuestion]
        );

        $this->getJson(
            '/api/get_question?class_id=7&subject=math&quarter=1&answered=' . $firstQuestion . ',' . $secondQuestion
        )
            ->assertOk()
            ->assertJsonPath('completed', true);
    }

    public function test_gameplay_question_lookup_rejects_an_unknown_subject(): void
    {
        $this->createQuestion('math', 1, null, 'exam');

        $this->getJson('/api/get_question?subject=history&type=exam&quarter=1')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('subject');
    }

    private function createQuestion(
        string $subject,
        int $quarter,
        ?string $difficulty = null,
        string $type = 'quiz',
        ?int $classId = null
    ): int
    {
        return DB::table('questions')->insertGetId([
            'class_id' => $classId,
            'subject' => $subject,
            'quarter' => $quarter,
            'type' => $type,
            'difficulty' => $difficulty,
            'question' => 'Sample question',
            'choice_a' => 'A',
            'choice_b' => 'B',
            'choice_c' => 'C',
            'choice_d' => 'D',
            'answer' => 'A',
            'explanation' => null,
        ]);
    }
}
