<?php

namespace Database\Seeders;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Illuminate\Database\Seeder;

class LocalizedContentSeeder extends Seeder
{
    public function run(): void
    {
        $categoryTranslations = [
            'إلكترونيات' => [
                'name_en' => 'Electronics',
                'name_fr' => 'Électronique',
                'description_en' => 'Phones, laptops, and tech accessories',
                'description_fr' => 'Téléphones, ordinateurs portables et accessoires technologiques',
            ],
            'ملابس رجالية' => [
                'name_en' => "Men's Clothing",
                'name_fr' => 'Vêtements Homme',
                'description_en' => 'Shirts, pants, and men\'s shoes',
                'description_fr' => 'Chemises, pantalons et chaussures homme',
            ],
            'ملابس نسائية' => [
                'name_en' => "Women's Clothing",
                'name_fr' => 'Vêtements Femme',
                'description_en' => 'Dresses, blouses, and women\'s shoes',
                'description_fr' => 'Robes, blouses et chaussures femme',
            ],
            'أحذية' => [
                'name_en' => 'Shoes',
                'name_fr' => 'Chaussures',
                'description_en' => 'Sports and classic shoes',
                'description_fr' => 'Chaussures sport et classiques',
            ],
            'إكسسوارات' => [
                'name_en' => 'Accessories',
                'name_fr' => 'Accessoires',
                'description_en' => 'Watches, bags, and jewelry',
                'description_fr' => 'Montres, sacs et bijoux',
            ],
            'منزل ومطبخ' => [
                'name_en' => 'Home & Kitchen',
                'name_fr' => 'Maison & Cuisine',
                'description_en' => 'Home and kitchen appliances',
                'description_fr' => 'Appareils pour la maison et la cuisine',
            ],
            'عطور وتجميل' => [
                'name_en' => 'Perfumes & Beauty',
                'name_fr' => 'Parfums & Beauté',
                'description_en' => 'Perfumes and cosmetics',
                'description_fr' => 'Parfums et cosmétiques',
            ],
            'ألعاب أطفال' => [
                'name_en' => 'Kids Toys',
                'name_fr' => 'Jouets Enfants',
                'description_en' => 'Educational and entertainment toys',
                'description_fr' => 'Jouets éducatifs et de divertissement',
            ],
        ];

        foreach ($categoryTranslations as $arabicName => $translations) {
            Category::where('name', $arabicName)->update($translations);
        }

        $productTranslations = [
            'هاتف سامسونج جالاكسي S24' => [
                'name_en' => 'Samsung Galaxy S24',
                'name_fr' => 'Samsung Galaxy S24',
                'short_description_en' => 'Smartphone with high specs and excellent camera',
                'short_description_fr' => 'Smartphone haute performance avec excellente caméra',
                'description_en' => 'Smartphone with high specs and excellent camera quality',
                'description_fr' => 'Smartphone haute performance avec caméra de qualité exceptionnelle',
            ],
            'لابتوب ديل XPS 13' => [
                'name_en' => 'Dell XPS 13 Laptop',
                'name_fr' => 'Ordinateur Dell XPS 13',
                'short_description_en' => 'Lightweight and elegant laptop for work and programming',
                'short_description_fr' => 'Ordinateur léger et élégant pour le travail et la programmation',
                'description_en' => 'Lightweight and elegant laptop for work and programming',
                'description_fr' => 'Ordinateur léger et élégant pour le travail et la programmation',
            ],
            'سماعات AirPods برو' => [
                'name_en' => 'AirPods Pro Headphones',
                'name_fr' => 'Écouteurs AirPods Pro',
                'short_description_en' => 'Wireless headphones with noise cancellation',
                'short_description_fr' => 'Écouteurs sans fil avec réduction de bruit',
                'description_en' => 'Wireless headphones with noise cancellation feature',
                'description_fr' => 'Écouteurs sans fil avec fonction de réduction de bruit',
            ],
            'شاحن سريع 65 واط' => [
                'name_en' => '65W Fast Charger',
                'name_fr' => 'Chargeur Rapide 65W',
                'short_description_en' => 'Fast charger compatible with all devices',
                'short_description_fr' => 'Chargeur rapide compatible avec tous les appareils',
                'description_en' => 'Fast charger compatible with all devices',
                'description_fr' => 'Chargeur rapide compatible avec tous les appareils',
            ],
            'قميص رسمي أزرق' => [
                'name_en' => 'Blue Formal Shirt',
                'name_fr' => 'Chemise Formelle Bleue',
                'short_description_en' => 'Formal shirt with high-quality cotton fabric',
                'short_description_fr' => 'Chemise formelle en tissu de coton de haute qualité',
                'description_en' => 'Formal shirt with high-quality cotton fabric',
                'description_fr' => 'Chemise formelle en tissu de coton de haute qualité',
            ],
            'بنطلون جينز كلاسيك' => [
                'name_en' => 'Classic Jeans Pants',
                'name_fr' => 'Pantalon Jean Classique',
                'short_description_en' => 'Durable and comfortable jeans',
                'short_description_fr' => 'Jean durable et confortable',
                'description_en' => 'Durable and comfortable jeans',
                'description_fr' => 'Jean durable et confortable',
            ],
            'بدلة رجالية سوداء' => [
                'name_en' => 'Black Men\'s Suit',
                'name_fr' => 'Costume Homme Noir',
                'short_description_en' => 'Formal suit for special occasions',
                'short_description_fr' => 'Costume formel pour les occasions spéciales',
                'description_en' => 'Formal suit for special occasions',
                'description_fr' => 'Costume formel pour les occasions spéciales',
            ],
            'فستان سهرة أحمر' => [
                'name_en' => 'Red Evening Dress',
                'name_fr' => 'Robe de Soirée Rouge',
                'short_description_en' => 'Elegant dress for special occasions',
                'short_description_fr' => 'Robe élégante pour les occasions spéciales',
                'description_en' => 'Elegant dress for special occasions',
                'description_fr' => 'Robe élégante pour les occasions spéciales',
            ],
            'بلوزة قطن صيفية' => [
                'name_en' => 'Summer Cotton Blouse',
                'name_fr' => 'Blouse d\'Été en Coton',
                'short_description_en' => 'Comfortable cotton blouse for summer',
                'short_description_fr' => 'Blouse en coton confortable pour l\'été',
                'description_en' => 'Comfortable cotton blouse for summer',
                'description_fr' => 'Blouse en coton confortable pour l\'été',
            ],
            'حقيبة يد جلدية' => [
                'name_en' => 'Leather Handbag',
                'name_fr' => 'Sac à Main en Cuir',
                'short_description_en' => 'Elegant handbag made of genuine leather',
                'short_description_fr' => 'Sac à main élégant en cuir véritable',
                'description_en' => 'Elegant handbag made of genuine leather',
                'description_fr' => 'Sac à main élégant en cuir véritable',
            ],
            'حذاء رياضي نايك' => [
                'name_en' => 'Nike Sports Shoe',
                'name_fr' => 'Chaussure Sport Nike',
                'short_description_en' => 'Comfortable sports shoe for running and daily use',
                'short_description_fr' => 'Chaussure de sport confortable pour la course et l\'usage quotidien',
                'description_en' => 'Comfortable sports shoe for running and daily use',
                'description_fr' => 'Chaussure de sport confortable pour la course et l\'usage quotidien',
            ],
            'حذاء كلاسيكي جلد' => [
                'name_en' => 'Classic Leather Shoe',
                'name_fr' => 'Chaussure Classique en Cuir',
                'short_description_en' => 'Genuine leather shoe for formal occasions',
                'short_description_fr' => 'Chaussure en cuir véritable pour les occasions formelles',
                'description_en' => 'Genuine leather shoe for formal occasions',
                'description_fr' => 'Chaussure en cuir véritable pour les occasions formelles',
            ],
            'طقم أواني طبخ 10 قطع' => [
                'name_en' => '10-Piece Cookware Set',
                'name_fr' => 'Set de Cuisine 10 Pièces',
                'short_description_en' => 'Stainless steel cookware set',
                'short_description_fr' => 'Ensemble de cuisine en acier inoxydable',
                'description_en' => 'Stainless steel cookware set',
                'description_fr' => 'Ensemble de cuisine en acier inoxydable',
            ],
            'مكنسة كهربائية ذكية' => [
                'name_en' => 'Smart Vacuum Cleaner',
                'name_fr' => 'Aspirateur Intelligent',
                'short_description_en' => 'Smart vacuum cleaner with remote control',
                'short_description_fr' => 'Aspirateur intelligent avec télécommande',
                'description_en' => 'Smart vacuum cleaner with remote control',
                'description_fr' => 'Aspirateur intelligent avec télécommande',
            ],
            'عطر ديور سوفاج' => [
                'name_en' => 'Dior Sauvage Perfume',
                'name_fr' => 'Parfum Dior Sauvage',
                'short_description_en' => 'Luxury men\'s perfume',
                'short_description_fr' => 'Parfum de luxe pour homme',
                'description_en' => 'Luxury men\'s perfume, authentic product 100% with guarantee',
                'description_fr' => 'Parfum de luxe pour homme, produit authentique 100% avec garantie',
            ],
            'مجموعة عناية بالبشرة' => [
                'name_en' => 'Skincare Set',
                'name_fr' => 'Kit Soins de la Peau',
                'short_description_en' => 'Complete set for daily skincare',
                'short_description_fr' => 'Ensemble complet pour les soins quotidiens de la peau',
                'description_en' => 'Complete set for daily skincare',
                'description_fr' => 'Ensemble complet pour les soins quotidiens de la peau',
            ],
        ];

        foreach ($productTranslations as $arabicName => $translations) {
            Product::where('name', $arabicName)->update($translations);
        }
    }
}
