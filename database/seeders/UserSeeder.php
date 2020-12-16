<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        $this->defaultUser();

        foreach (range(1, 20) as $index) {
            User::create([
                'name'     => $faker->name,
                'email'    => $faker->unique()->email,
                'password' => bcrypt('123456')
            ]);
        }
    }

    private function defaultUser()
    {
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@gmail.com',
            'password' => bcrypt('123456')
        ]);
    }
}
