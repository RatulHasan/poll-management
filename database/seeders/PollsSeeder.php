<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class PollsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@example.com')->first()
                     ?? User::factory()->create(['email' => 'admin@example.com', 'name' => 'Admin']);

        $polls = [
            [
                'title' => 'What is your favorite programming language?',
                'description' => 'Choose the language you enjoy coding with the most.',
                'options' => ['Python', 'JavaScript', 'PHP', 'Java', 'C#'],
            ],
            [
                'title' => 'Which social media platform do you use the most?',
                'description' => 'Pick the one you spend the most time on.',
                'options' => ['Facebook', 'Instagram', 'Twitter/X', 'LinkedIn', 'TikTok'],
            ],
            [
                'title' => 'What type of cuisine do you prefer?',
                'description' => 'Select your favorite food category.',
                'options' => ['Italian', 'Chinese', 'Mexican', 'Indian', 'Japanese', 'Mediterranean'],
            ],
            [
                'title' => 'Which mobile OS do you prefer?',
                'description' => 'Pick your most used smartphone operating system.',
                'options' => ['iOS', 'Android', 'HarmonyOS', 'Other'],
            ],
            [
                'title' => 'What is your preferred work style?',
                'description' => 'Choose the style that suits you best.',
                'options' => ['Remote', 'Hybrid', 'On-site'],
            ],
            [
                'title' => 'Which cloud provider do you prefer?',
                'description' => 'Pick your go-to cloud hosting platform.',
                'options' => ['AWS', 'Google Cloud', 'Microsoft Azure', 'DigitalOcean', 'Linode'],
            ],
            [
                'title' => 'What is your favorite web framework?',
                'description' => 'Choose the one you like working with.',
                'options' => ['Laravel', 'Django', 'Ruby on Rails', 'Spring Boot', 'Express.js'],
            ],
            [
                'title' => 'Which streaming service do you use most?',
                'description' => 'Pick your favorite video streaming platform.',
                'options' => ['Netflix', 'Amazon Prime', 'Disney+', 'HBO Max', 'YouTube Premium'],
            ],
            [
                'title' => 'What type of pet do you prefer?',
                'description' => 'Choose the pet you like the most.',
                'options' => ['Dog', 'Cat', 'Bird', 'Fish', 'Reptile'],
            ],
            [
                'title' => 'Which sport do you enjoy watching?',
                'description' => 'Pick your favorite spectator sport.',
                'options' => ['Football/Soccer', 'Cricket', 'Basketball', 'Tennis', 'Baseball', 'Rugby'],
            ],
            [
                'title' => 'What is your preferred coffee type?',
                'description' => 'How do you like your daily caffeine fix?',
                'options' => ['Espresso', 'Cappuccino', 'Latte', 'Black Coffee', 'Cold Brew'],
            ],
            [
                'title' => 'Which movie genre do you enjoy most?',
                'description' => 'Select your go-to entertainment category.',
                'options' => ['Action', 'Comedy', 'Drama', 'Horror', 'Sci-Fi', 'Romance'],
            ],
            [
                'title' => 'What is your ideal vacation type?',
                'description' => 'How do you prefer to spend your holidays?',
                'options' => ['Beach Resort', 'Mountain Adventure', 'City Exploration', 'Cultural Tours', 'Cruise'],
            ],
            [
                'title' => 'Which transportation method do you use daily?',
                'description' => 'Your primary mode of getting around.',
                'options' => ['Car', 'Public Transport', 'Bicycle', 'Walking', 'Motorcycle'],
            ],
            [
                'title' => 'What is your favorite season?',
                'description' => 'Which time of year do you enjoy the most?',
                'options' => ['Spring', 'Summer', 'Autumn/Fall', 'Winter'],
            ],
            [
                'title' => 'Which music genre do you listen to most?',
                'description' => 'Pick your preferred musical style.',
                'options' => ['Pop', 'Rock', 'Hip-Hop', 'Classical', 'Jazz', 'Electronic'],
            ],
            [
                'title' => 'What is your preferred learning method?',
                'description' => 'How do you best absorb new information?',
                'options' => ['Visual', 'Auditory', 'Hands-on', 'Reading', 'Group Discussion'],
            ],
            [
                'title' => 'Which gaming platform do you prefer?',
                'description' => 'Where do you spend most of your gaming time?',
                'options' => ['PC', 'PlayStation', 'Xbox', 'Nintendo Switch', 'Mobile'],
            ],
            [
                'title' => 'What is your ideal work schedule?',
                'description' => 'When are you most productive?',
                'options' => ['9-to-5 Traditional', 'Early Morning Start', 'Late Night', 'Flexible Hours'],
            ],
            [
                'title' => 'Which social cause matters most to you?',
                'description' => 'Select the issue you care about deeply.',
                'options' => ['Climate Change', 'Education', 'Healthcare', 'Poverty', 'Human Rights', 'Animal Welfare'],
            ],
            [
                'title' => 'What is your preferred exercise type?',
                'description' => 'How do you like to stay fit?',
                'options' => ['Cardio', 'Weight Training', 'Yoga', 'Swimming', 'Team Sports'],
            ],
            [
                'title' => 'Which news source do you trust most?',
                'description' => 'Where do you get your daily news?',
                'options' => ['Traditional TV News', 'Online News Websites', 'Social Media', 'Newspapers', 'Podcasts'],
            ],
            [
                'title' => 'What is your favorite book genre?',
                'description' => 'Which type of books do you enjoy reading?',
                'options' => ['Fiction', 'Non-Fiction', 'Mystery/Thriller', 'Biography', 'Self-Help', 'Fantasy/Sci-Fi'],
            ],
            [
                'title' => 'Which payment method do you use most?',
                'description' => 'Your preferred way to make purchases.',
                'options' => ['Credit Card', 'Debit Card', 'Digital Wallet', 'Cash', 'Bank Transfer'],
            ],
            [
                'title' => 'What is your ideal team size for projects?',
                'description' => 'How many people work best together?',
                'options' => ['Solo (1 person)', 'Small (2-3 people)', 'Medium (4-6 people)', 'Large (7+ people)'],
            ],
            [
                'title' => 'Which technology trend excites you most?',
                'description' => 'What innovation are you most interested in?',
                'options' => ['Artificial Intelligence', 'Virtual Reality', 'Blockchain', 'Internet of Things', 'Renewable Energy'],
            ],
            [
                'title' => 'What is your preferred meeting format?',
                'description' => 'How do you like to conduct business meetings?',
                'options' => ['Video Call', 'In-Person', 'Phone Call', 'Email Discussion', 'Instant Messaging'],
            ],
            [
                'title' => 'Which shopping method do you prefer?',
                'description' => 'How do you like to make purchases?',
                'options' => ['Online Shopping', 'In-Store', 'Mobile Apps', 'Phone Orders', 'Social Commerce'],
            ],
            [
                'title' => 'What motivates you most at work?',
                'description' => 'What drives your professional performance?',
                'options' => ['Career Growth', 'Financial Rewards', 'Work-Life Balance', 'Team Collaboration', 'Creative Freedom'],
            ],
            [
                'title' => 'Which communication style do you prefer?',
                'description' => 'How do you like to interact with others?',
                'options' => ['Direct and Brief', 'Detailed and Thorough', 'Casual and Friendly', 'Formal and Professional', 'Visual and Creative'],
            ],
        ];

        foreach ($polls as $index => $pollData) {
            $poll = Poll::create([
                'user_id' => $adminUser->id,
                'title' => $pollData['title'],
                'description' => $pollData['description'],
                'is_active' => true,
                'expires_at' => fake()->boolean(70) ? now()->addDays(fake()->numberBetween(7, 60)) : null,
            ]);

            foreach ($pollData['options'] as $order => $optionText) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'text' => $optionText,
                    'order' => $order,
                ]);
            }

            $this->command->info("Created poll: {$pollData['title']}");
        }

        $this->command->info('Successfully created 30 polls with their options!');
    }
}
