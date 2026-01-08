<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create Admin User
        $admin = new User();
        $admin->setEmail('admin@shop.com');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        // Create Regular User
        $user = new User();
        $user->setEmail('user@shop.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        // Create Categories
        $plushCategory = new Category();
        $plushCategory->setName('Плюшени играчки');
        $manager->persist($plushCategory);

        $woodenCategory = new Category();
        $woodenCategory->setName('Дървени изделия');
        $manager->persist($woodenCategory);

        $interactiveCategory = new Category();
        $interactiveCategory->setName('Интерактивни играчки');
        $manager->persist($interactiveCategory);

        $homeDecorCategory = new Category();
        $homeDecorCategory->setName('Декорация за дома');
        $manager->persist($homeDecorCategory);

        $accessoriesCategory = new Category();
        $accessoriesCategory->setName('Аксесоари');
        $manager->persist($accessoriesCategory);

        // Create 20 Products with Categories
        $productsData = [
            ['name' => 'Classic Teddy Bear', 'description' => 'Soft and cuddly plush teddy bear, perfect for children of all ages. Handmade with love.', 'price' => '24.99', 'stock' => 15, 'category' => $plushCategory],
            ['name' => 'Bunny Plush Toy', 'description' => 'Adorable bunny plush with floppy ears. Made from premium soft materials.', 'price' => '19.99', 'stock' => 20, 'category' => $plushCategory],
            ['name' => 'Wooden Puzzle Set', 'description' => 'Educational wooden puzzle with colorful pieces. Great for developing motor skills.', 'price' => '29.99', 'stock' => 12, 'category' => $woodenCategory],
            ['name' => 'Elephant Stuffed Animal', 'description' => 'Large elephant plush toy with embroidered details. Safe for babies and toddlers.', 'price' => '34.99', 'stock' => 8, 'category' => $plushCategory],
            ['name' => 'Wooden Building Blocks', 'description' => 'Set of 50 wooden building blocks in various shapes and colors. Eco-friendly and durable.', 'price' => '39.99', 'stock' => 10, 'category' => $woodenCategory],
            ['name' => 'Toy Kitchen Set', 'description' => 'Complete wooden kitchen playset with accessories. Encourages imaginative play.', 'price' => '89.99', 'stock' => 5, 'category' => $interactiveCategory],
            ['name' => 'Unicorn Plush', 'description' => 'Magical unicorn plush with rainbow mane and sparkly horn. A favorite among kids!', 'price' => '27.99', 'stock' => 18, 'category' => $plushCategory],
            ['name' => 'Wooden Train Set', 'description' => 'Classic wooden train set with tracks and carriages. Compatible with major brands.', 'price' => '49.99', 'stock' => 7, 'category' => $woodenCategory],
            ['name' => 'Giraffe Stuffed Toy', 'description' => 'Tall giraffe plush with soft spotted pattern. Perfect cuddle companion.', 'price' => '32.99', 'stock' => 14, 'category' => $plushCategory],
            ['name' => 'Wooden Rocking Horse', 'description' => 'Handcrafted wooden rocking horse with smooth finish. Heirloom quality.', 'price' => '129.99', 'stock' => 3, 'category' => $woodenCategory],
            ['name' => 'Dinosaur Plush Collection', 'description' => 'Set of 3 dinosaur plushies - T-Rex, Triceratops, and Stegosaurus. Roar-some fun!', 'price' => '44.99', 'stock' => 11, 'category' => $plushCategory],
            ['name' => 'Wooden Tool Bench', 'description' => 'Interactive wooden tool bench with hammer, screwdriver, and more. Build and create!', 'price' => '59.99', 'stock' => 6, 'category' => $interactiveCategory],
            ['name' => 'Knitted Baby Blanket', 'description' => 'Soft, handmade knitted blanket perfect for babies. Machine washable and hypoallergenic.', 'price' => '45.00', 'stock' => 9, 'category' => $homeDecorCategory],
            ['name' => 'Ceramic Mug Set', 'description' => 'Set of 4 handpainted ceramic mugs. Each one is unique and dishwasher safe.', 'price' => '35.00', 'stock' => 16, 'category' => $homeDecorCategory],
            ['name' => 'Leather Wallet', 'description' => 'Handcrafted genuine leather wallet with multiple card slots. Ages beautifully with use.', 'price' => '55.00', 'stock' => 13, 'category' => $accessoriesCategory],
            ['name' => 'Macrame Wall Hanging', 'description' => 'Beautiful bohemian macrame wall art. Adds texture and warmth to any room.', 'price' => '38.50', 'stock' => 7, 'category' => $homeDecorCategory],
            ['name' => 'Wooden Yo-Yo', 'description' => 'Classic wooden yo-yo with smooth action. Great for beginners and collectors.', 'price' => '12.99', 'stock' => 25, 'category' => $interactiveCategory],
            ['name' => 'Handmade Soap Set', 'description' => 'Set of 5 natural handmade soaps with essential oils. Vegan and cruelty-free.', 'price' => '22.50', 'stock' => 30, 'category' => $accessoriesCategory],
            ['name' => 'Embroidered Pillow Cover', 'description' => 'Hand-embroidered cushion cover with floral design. Adds elegance to your living space.', 'price' => '28.00', 'stock' => 12, 'category' => $homeDecorCategory],
            ['name' => 'Wooden Picture Frame', 'description' => 'Rustic wooden photo frame for 5x7 photos. Handcrafted from reclaimed wood.', 'price' => '18.99', 'stock' => 20, 'category' => $homeDecorCategory]
        ];

        foreach ($productsData as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setStock($productData['stock']);
            $product->setCategory($productData['category']);
            $product->setImageFilename('https://placehold.co/400x400/40E0D0/white?text=' . urlencode($productData['name']));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
