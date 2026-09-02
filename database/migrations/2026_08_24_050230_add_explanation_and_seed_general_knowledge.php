<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add explanation column
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'explanation')) {
                $table->text('explanation')->nullable()->after('answer');
            }
        });

        // 2. Seed 45 general knowledge questions (15 per subject)
        $questions = [
            // === MATHEMATICS (15 Questions) ===
            [
                'subject' => 'math',
                'question' => 'What is the value of Pi (π) rounded to two decimal places?',
                'choice_a' => '3.12', 'choice_b' => '3.14', 'choice_c' => '3.16', 'choice_d' => '3.18',
                'answer' => 'B',
                'explanation' => 'Pi (π) is the ratio of a circle\'s circumference to its diameter. Its approximate value is 3.14159..., which rounds to 3.14.',
            ],
            [
                'subject' => 'math',
                'question' => 'If a triangle has a base of 6 cm and a height of 10 cm, what is its area?',
                'choice_a' => '60 sq cm', 'choice_b' => '30 sq cm', 'choice_c' => '15 sq cm', 'choice_d' => '20 sq cm',
                'answer' => 'B',
                'explanation' => 'The area of a triangle is calculated as (Base × Height) / 2. Here, (6 × 10) / 2 = 30 sq cm.',
            ],
            [
                'subject' => 'math',
                'question' => 'What is the square root of 144?',
                'choice_a' => '10', 'choice_b' => '11', 'choice_c' => '12', 'choice_d' => '14',
                'answer' => 'C',
                'explanation' => 'The square root of 144 is 12 because 12 multiplied by 12 equals 144.',
            ],
            [
                'subject' => 'math',
                'question' => 'Solve for x: 3x - 7 = 14',
                'choice_a' => '5', 'choice_b' => '6', 'choice_c' => '7', 'choice_d' => '8',
                'answer' => 'C',
                'explanation' => 'Adding 7 to both sides gives 3x = 21. Dividing both sides by 3 yields x = 7.',
            ],
            [
                'subject' => 'math',
                'question' => 'What is the next prime number after 7?',
                'choice_a' => '9', 'choice_b' => '11', 'choice_c' => '13', 'choice_d' => '15',
                'answer' => 'B',
                'explanation' => 'A prime number is a number divisible only by 1 and itself. 8, 9, and 10 are composite, so 11 is the next prime.',
            ],
            [
                'subject' => 'math',
                'question' => 'What is 2 to the power of 5 (2^5)?',
                'choice_a' => '16', 'choice_b' => '32', 'choice_c' => '64', 'choice_d' => '10',
                'answer' => 'B',
                'explanation' => '2^5 = 2 × 2 × 2 × 2 × 2 = 32.',
            ],
            [
                'subject' => 'math',
                'question' => 'How many degrees are in a right angle?',
                'choice_a' => '45', 'choice_b' => '90', 'choice_c' => '180', 'choice_d' => '360',
                'answer' => 'B',
                'explanation' => 'A right angle is an angle of exactly 90 degrees, corresponding to a quarter turn.',
            ],
            [
                'subject' => 'math',
                'question' => 'If x = 4 and y = 5, what is the value of 2x + 3y?',
                'choice_a' => '20', 'choice_b' => '23', 'choice_c' => '25', 'choice_d' => '28',
                'answer' => 'B',
                'explanation' => 'Substituting the values: 2(4) + 3(5) = 8 + 15 = 23.',
            ],
            [
                'subject' => 'math',
                'question' => 'What is the Roman numeral for 90?',
                'choice_a' => 'LXXXX', 'choice_b' => 'XC', 'choice_c' => 'CX', 'choice_d' => 'XL',
                'answer' => 'B',
                'explanation' => 'In Roman numerals, C is 100. Placing X (10) before C subtracts 10, resulting in XC = 90.',
            ],
            [
                'subject' => 'math',
                'question' => 'What is 15% of 200?',
                'choice_a' => '15', 'choice_b' => '30', 'choice_c' => '45', 'choice_d' => '60',
                'answer' => 'B',
                'explanation' => '15% of 200 is calculated as (15 / 100) × 200 = 15 × 2 = 30.',
            ],
            [
                'subject' => 'math',
                'question' => 'How many seconds are in one hour?',
                'choice_a' => '60', 'choice_b' => '120', 'choice_c' => '3600', 'choice_d' => '2400',
                'answer' => 'C',
                'explanation' => 'One hour has 60 minutes, and each minute has 60 seconds. So, 60 × 60 = 3600 seconds.',
            ],
            [
                'subject' => 'math',
                'question' => 'What is the sum of the angles in a quadrilateral?',
                'choice_a' => '180 degrees', 'choice_b' => '270 degrees', 'choice_c' => '360 degrees', 'choice_d' => '540 degrees',
                'answer' => 'C',
                'explanation' => 'Any quadrilateral can be divided into two triangles. Since each triangle has 180 degrees, the total sum is 2 × 180 = 360 degrees.',
            ],
            [
                'subject' => 'math',
                'question' => 'Which of these numbers is divisible by 3?',
                'choice_a' => '124', 'choice_b' => '256', 'choice_c' => '372', 'choice_d' => '413',
                'answer' => 'C',
                'explanation' => 'A number is divisible by 3 if the sum of its digits is divisible by 3. For 372: 3 + 7 + 2 = 12, which is divisible by 3.',
            ],
            [
                'subject' => 'math',
                'question' => 'What is the median of the following set: 3, 7, 9, 4, 5?',
                'choice_a' => '9', 'choice_b' => '7', 'choice_c' => '5', 'choice_d' => '4',
                'answer' => 'C',
                'explanation' => 'First, arrange the numbers in order: 3, 4, 5, 7, 9. The middle number is 5, which is the median.',
            ],
            [
                'subject' => 'math',
                'question' => 'What is 0 divided by 5?',
                'choice_a' => '0', 'choice_b' => '5', 'choice_c' => 'Undefined', 'choice_d' => '1',
                'answer' => 'A',
                'explanation' => 'Dividing zero by any non-zero number yields zero, because zero items shared among 5 people means everyone gets zero.',
            ],

            // === SCIENCE (15 Questions) ===
            [
                'subject' => 'science',
                'question' => 'What is the chemical symbol for Gold?',
                'choice_a' => 'Gd', 'choice_b' => 'Go', 'choice_c' => 'Au', 'choice_d' => 'Ag',
                'answer' => 'C',
                'explanation' => 'The chemical symbol for Gold is Au, derived from the Latin word "aurum", meaning shining dawn.',
            ],
            [
                'subject' => 'science',
                'question' => 'Which planet is known as the Red Planet?',
                'choice_a' => 'Venus', 'choice_b' => 'Mars', 'choice_c' => 'Jupiter', 'choice_d' => 'Saturn',
                'answer' => 'B',
                'explanation' => 'Mars is known as the Red Planet due to the iron oxide (rust) on its surface, giving it a reddish appearance.',
            ],
            [
                'subject' => 'science',
                'question' => 'What is the powerhouse of the cell?',
                'choice_a' => 'Nucleus', 'choice_b' => 'Ribosome', 'choice_c' => 'Mitochondria', 'choice_d' => 'Cytoplasm',
                'answer' => 'C',
                'explanation' => 'Mitochondria are known as the powerhouses of the cell because they generate most of the cell\'s supply of adenosine triphosphate (ATP), used as chemical energy.',
            ],
            [
                'subject' => 'science',
                'question' => 'What gas do plants absorb from the atmosphere for photosynthesis?',
                'choice_a' => 'Oxygen', 'choice_b' => 'Nitrogen', 'choice_c' => 'Carbon Dioxide', 'choice_d' => 'Hydrogen',
                'answer' => 'C',
                'explanation' => 'During photosynthesis, plants absorb Carbon Dioxide (CO2) and water, using sunlight to convert them into glucose and oxygen.',
            ],
            [
                'subject' => 'science',
                'question' => 'What is the boiling point of water at standard atmospheric pressure?',
                'choice_a' => '90°C', 'choice_b' => '100°C', 'choice_c' => '120°C', 'choice_d' => '80°C',
                'answer' => 'B',
                'explanation' => 'Under normal conditions at sea level, water boils at exactly 100 degrees Celsius (or 212 degrees Fahrenheit).',
            ],
            [
                'subject' => 'science',
                'question' => 'Which is the largest organ in the human body?',
                'choice_a' => 'Liver', 'choice_b' => 'Skin', 'choice_c' => 'Brain', 'choice_d' => 'Heart',
                'answer' => 'B',
                'explanation' => 'The skin is the body\'s largest organ, covering the entire body surface and accounting for about 16% of body weight.',
            ],
            [
                'subject' => 'science',
                'question' => 'What force pulls objects toward the center of the Earth?',
                'choice_a' => 'Magnetism', 'choice_b' => 'Friction', 'choice_c' => 'Gravity', 'choice_d' => 'Tension',
                'answer' => 'C',
                'explanation' => 'Gravity is the force of attraction that exists between all objects with mass. Earth\'s gravity pulls objects downward toward its center.',
            ],
            [
                'subject' => 'science',
                'question' => 'What is the closest star to Earth?',
                'choice_a' => 'Proxima Centauri', 'choice_b' => 'The Sun', 'choice_c' => 'Sirius', 'choice_d' => 'Polaris',
                'answer' => 'B',
                'explanation' => 'The Sun is the closest star to Earth, located about 93 million miles away. Proxima Centauri is the closest star outside our solar system.',
            ],
            [
                'subject' => 'science',
                'question' => 'Which gas is most abundant in Earth\'s atmosphere?',
                'choice_a' => 'Oxygen', 'choice_b' => 'Carbon Dioxide', 'choice_c' => 'Nitrogen', 'choice_d' => 'Argon',
                'answer' => 'C',
                'explanation' => 'Nitrogen makes up about 78% of Earth\'s atmosphere, while Oxygen makes up about 21%.',
            ],
            [
                'subject' => 'science',
                'question' => 'What system is responsible for pumping blood throughout the body?',
                'choice_a' => 'Respiratory System', 'choice_b' => 'Nervous System', 'choice_c' => 'Circulatory System', 'choice_d' => 'Digestive System',
                'answer' => 'C',
                'explanation' => 'The Circulatory (cardiovascular) System, consisting of the heart and blood vessels, pumps blood, oxygen, and nutrients throughout the body.',
            ],
            [
                'subject' => 'science',
                'question' => 'How long does it take for Earth to complete one revolution around the Sun?',
                'choice_a' => '24 hours', 'choice_b' => '30 days', 'choice_c' => '365.25 days', 'choice_d' => '28 days',
                'answer' => 'C',
                'explanation' => 'It takes Earth approximately 365.25 days to orbit the Sun, which is why we add a leap day (February 29) every four years.',
            ],
            [
                'subject' => 'science',
                'question' => 'Which state of matter has a definite volume but no definite shape?',
                'choice_a' => 'Solid', 'choice_b' => 'Liquid', 'choice_c' => 'Gas', 'choice_d' => 'Plasma',
                'answer' => 'B',
                'explanation' => 'Liquids have a fixed volume but take the shape of whatever container they are placed in.',
            ],
            [
                'subject' => 'science',
                'question' => 'What is the process of water changing into gas or vapor?',
                'choice_a' => 'Condensation', 'choice_b' => 'Evaporation', 'choice_c' => 'Precipitation', 'choice_d' => 'Freezing',
                'answer' => 'B',
                'explanation' => 'Evaporation is the process by which liquid water absorbs heat energy and turns into gaseous water vapor.',
            ],
            [
                'subject' => 'science',
                'question' => 'What type of animal eats only plants?',
                'choice_a' => 'Carnivore', 'choice_b' => 'Herbivore', 'choice_c' => 'Omnivore', 'choice_d' => 'Insectivore',
                'answer' => 'B',
                'explanation' => 'Herbivores are animals (like cows, rabbits, and deer) whose diet consists entirely of vegetation.',
            ],
            [
                'subject' => 'science',
                'question' => 'What is the speed of light?',
                'choice_a' => '3,000 km/s', 'choice_b' => '30,000 km/s', 'choice_c' => '300,000 km/s', 'choice_d' => '3,000,000 km/s',
                'answer' => 'C',
                'explanation' => 'The speed of light in a vacuum is approximately 299,792 kilometers per second (commonly rounded to 300,000 km/s).',
            ],

            // === ENGLISH (15 Questions) ===
            [
                'subject' => 'english',
                'question' => 'Which of the following is a noun?',
                'choice_a' => 'Run', 'choice_b' => 'Beautiful', 'choice_c' => 'Castle', 'choice_d' => 'Quickly',
                'answer' => 'C',
                'explanation' => 'A noun is a person, place, thing, or idea. "Castle" is a place/thing, while "Run" is a verb, "Beautiful" is an adjective, and "Quickly" is an adverb.',
            ],
            [
                'subject' => 'english',
                'question' => 'What is the past tense of the verb "go"?',
                'choice_a' => 'Goed', 'choice_b' => 'Gone', 'choice_c' => 'Went', 'choice_d' => 'Going',
                'answer' => 'C',
                'explanation' => 'Go is an irregular verb. Its past simple form is "went" (e.g., "I went to the store yesterday").',
            ],
            [
                'subject' => 'english',
                'question' => 'Identify the adjective in this sentence: "The brave knight fought the dragon."',
                'choice_a' => 'Knight', 'choice_b' => 'Brave', 'choice_c' => 'Fought', 'choice_d' => 'Dragon',
                'answer' => 'B',
                'explanation' => 'An adjective modifies or describes a noun. "Brave" describes the noun "knight".',
            ],
            [
                'subject' => 'english',
                'question' => 'What is a synonym for the word "Reluctant"?',
                'choice_a' => 'Eager', 'choice_b' => 'Unwilling', 'choice_c' => 'Happy', 'choice_d' => 'Fearless',
                'answer' => 'B',
                'explanation' => '"Reluctant" means feeling or showing hesitation or unwillingness. Therefore, "unwilling" is its synonym.',
            ],
            [
                'subject' => 'english',
                'question' => 'Which of the following is a compound word?',
                'choice_a' => 'Running', 'choice_b' => 'Beautiful', 'choice_c' => 'Sunshine', 'choice_d' => 'Carefully',
                'answer' => 'C',
                'explanation' => 'A compound word is formed by joining two distinct words. "Sun" and "shine" combine to form "Sunshine".',
            ],
            [
                'subject' => 'english',
                'question' => 'Choose the correct spelling:',
                'choice_a' => 'Recieve', 'choice_b' => 'Receive', 'choice_c' => 'Recive', 'choice_d' => 'Receve',
                'answer' => 'B',
                'explanation' => 'The spelling rule is "I before E except after C". Since it follows a C, the correct spelling is "Receive".',
            ],
            [
                'subject' => 'english',
                'question' => 'What is the plural form of the word "Mouse"?',
                'choice_a' => 'Mouses', 'choice_b' => 'Mice', 'choice_c' => 'Mices', 'choice_d' => 'Mouse',
                'answer' => 'B',
                'explanation' => '"Mouse" has an irregular plural form, which is "Mice".',
            ],
            [
                'subject' => 'english',
                'question' => 'Identify the pronoun in this sentence: "She went to the market."',
                'choice_a' => 'Went', 'choice_b' => 'She', 'choice_c' => 'Market', 'choice_d' => 'The',
                'answer' => 'B',
                'explanation' => 'A pronoun replaces a noun. "She" is a personal pronoun representing a female subject.',
            ],
            [
                'subject' => 'english',
                'question' => 'What is an antonym for the word "Ancient"?',
                'choice_a' => 'Old', 'choice_b' => 'Modern', 'choice_c' => 'Historic', 'choice_d' => 'Golden',
                'answer' => 'B',
                'explanation' => '"Ancient" means very old. Its opposite (antonym) is "Modern".',
            ],
            [
                'subject' => 'english',
                'question' => 'What punctuation mark is used to show ownership or contraction?',
                'choice_a' => 'Comma', 'choice_b' => 'Apostrophe', 'choice_c' => 'Hyphen', 'choice_d' => 'Colon',
                'answer' => 'B',
                'explanation' => 'An apostrophe (\') is used for possession (e.g., "knight\'s sword") and contractions (e.g., "do not" becomes "don\'t").',
            ],
            [
                'subject' => 'english',
                'question' => 'Identify the conjunction in the sentence: "I wanted to play, but it started raining."',
                'choice_a' => 'Wanted', 'choice_b' => 'But', 'choice_c' => 'Play', 'choice_d' => 'Raining',
                'answer' => 'B',
                'explanation' => 'A conjunction joins words, phrases, or clauses. "But" is a coordinating conjunction linking two independent clauses.',
            ],
            [
                'subject' => 'english',
                'question' => 'What is the comparative form of the adjective "Good"?',
                'choice_a' => 'Gooder', 'choice_b' => 'Best', 'choice_c' => 'Better', 'choice_d' => 'More good',
                'answer' => 'C',
                'explanation' => '"Good" is irregular. Its comparative form is "Better" (used to compare two things) and its superlative form is "Best".',
            ],
            [
                'subject' => 'english',
                'question' => 'Identify the adverb in this sentence: "The horse ran quickly."',
                'choice_a' => 'Horse', 'choice_b' => 'Ran', 'choice_c' => 'Quickly', 'choice_d' => 'The',
                'answer' => 'C',
                'explanation' => 'An adverb describes or modifies a verb, adjective, or other adverb. "Quickly" describes how the horse "ran".',
            ],
            [
                'subject' => 'english',
                'question' => 'What is a person, place, or thing that is written with a capital letter called?',
                'choice_a' => 'Common Noun', 'choice_b' => 'Proper Noun', 'choice_c' => 'Pronoun', 'choice_d' => 'Verb',
                'answer' => 'B',
                'explanation' => 'Proper nouns name specific people, places, or brands (e.g. "Clash of Subjects", "Arthur") and always start with capital letters.',
            ],
            [
                'subject' => 'english',
                'question' => 'Complete the common idiom: "A piece of ___" (meaning very easy).',
                'choice_a' => 'Bread', 'choice_b' => 'Cake', 'choice_c' => 'Pie', 'choice_d' => 'Paper',
                'answer' => 'B',
                'explanation' => '"A piece of cake" is a common English idiom that means something is very easy to do.',
            ],
        ];

        $rows = [];
        foreach ($questions as $q) {
            $rows[] = [
                'class_id' => null,
                'subject' => $q['subject'],
                'type' => 'quiz',
                'quarter' => 1,
                'sequence_number' => 1,
                'question' => $q['question'],
                'choice_a' => $q['choice_a'],
                'choice_b' => $q['choice_b'],
                'choice_c' => $q['choice_c'],
                'choice_d' => $q['choice_d'],
                'answer' => $q['answer'],
                'explanation' => $q['explanation'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('questions')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Remove seeded questions
        DB::table('questions')
            ->whereNull('class_id')
            ->whereIn('subject', ['math', 'science', 'english'])
            ->where('type', 'quiz')
            ->whereNotNull('explanation')
            ->delete();

        // 2. Remove explanation column
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'explanation')) {
                $table->dropColumn('explanation');
            }
        });
    }
};
