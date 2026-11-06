# Projet SIO-Shoes : site « e-commerce » avec Symfony

## Partie 11 : filtrer les produits en fonction des catégories

### 1. Modifiez le contrôleur « src\Controller\HomeController.php » pour afficher la liste des catégories dans la route « app_home ».

Commencez par importer le repository « CategoryRepository » :

```php
use App\Repository\CategoryRepository;
```

Modifiez ensuite le code de la route « app_home » pour ajouter les catégories :

```php
[Route('/', name: 'app_home',  methods: ['GET'])]
public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
{
        return $this->render('home/index.html.twig', [
            'products' => $productRepository->findBy([],['name'=>"ASC"]),
            'categories' => $categoryRepository->findAll(),
        ]);
}
```

### 2. Modifiez le contrôleur « src\Controller\HomeController.php » pour créer une nouvelle route « app_home_product_filter ».

Commencez par importer le repository « SubCategoryRepository » :

```php
use App\Repository\SubCategoryRepository;
```

Ajoutez ensuite le code suivant au contrôleur :

```php
#[Route('/home/product/subcategory/{id}/filter', name: 'app_home_product_filter',  methods: ['GET'])]
public function filter($id, CategoryRepository $categoryRepository, SubCategoryRepository $subCategoryRepository): Response
{
        $products = $subCategoryRepository->find($id)->getProducts();
        $category = $subCategoryRepository->find($id)->getCategory();
        $subCategory = $subCategoryRepository->find($id);

        return $this->render('home/filter.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
            'category' => $category,
            'subCategory' => $subCategory,
        ]);
}
```

> **Remarques :** on utilise ici la méthode « getProducts() » de l'entité « src\Entity\SubCategory.php » pour récupérer les produits filtrés. On récupère également la catégorie et la sous-catégorie pour l'affichage dans la vue.

### 3. Modifiez la route « app_home_product_show » pour ajouter les catégories qui doivent apparaitre dans le menu :

```php
#[Route('/home/product/{id}/show', name: 'app_home_product_show',  methods: ['GET'])]
public function show($id, Product $product, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
{
        $lastProducts = $productRepository->findBy([],['id'=>'DESC'],limit: 5);

        return $this ->render('home/show.html.twig', [
            'product' => $product,
            'products' => $lastProducts,
            'categories' => $categoryRepository->findAll().
        ]);
}
```

> **TRAVAIL A FAIRE POUR LES ETUDIANTS :**
> - Envoyer la liste des catégories à toutes les vues qui utilisent le template « templates\layouts\nav.html.twig »

### 4. Créez une vue « templates\home\filter.html.twig » avec le code suivant (on peut reprendre comme base le code du fichier « templates\home\index.html.twig ») :

```twig
{% extends 'base.html.twig' %}

{% block title %}Home{% endblock %}

{% block body %}
<div class="container">
<br>
    <h1>{{ subCategory.name }} {{ category.name }}</h1>
    <div class="row">
        {% for product in products %}
            <div class="col-md-3 mt-4">
                <div class="card" style="width: 20vh;">

                    {% if product.image %}
                        <a href="{{ path('app_home_product_show', {'id': product.id}) }}">
                        <img src="{{ asset('uploads/images/' ~ product.image) }}"
                        class="card-img-top" alt="{{ product.name }}"> </a>
                    {% else %}
                        <img src="{{ asset ('uploads/images/no_image_available_500x350.png')
                        }}" class="card-img-top" alt="Placeholder image">
                    {% endif %}
                    <div class="card-body">
                        <a style="text-decoration: none" href="{{
                        path('app_home_product_show', {'id': product.id}) }}"> <h5
                        class="card-title">{{product.name}}</h5> </a>
                        <p class="card-text text-truncate">{{ product.description }}</p>
                        <h3>{{ product.price}} €</h3>
                        <a href="#" class="btn btn-outline-success">Ajouter au panier</a>
                    </div>
                </div>
            </div>
        {% else %}
            <p>Aucun produit disponible.</p>
        {% endfor %}
    </div>
</div>
{% endblock %}
```

### 5. Ajout de la liste des catégories dans le menu.

Allez dans le fichier « templates\layouts\nav.html.twig » et ajoutez un menu dropdown :

```twig
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button"
data-bs-toggle="dropdown" aria-expanded="false">Catégories</a>
<ul class="dropdown-menu">
    {%for category in categories %}
            <li>
            <a class="dropdown-item text-success" href="#">{{ category.name }}</a>
            </li>
            {%for subCategory in category.subCategories %}
            <li>
                    <a class="dropdown-item" href="{{ path('app_home_product_filter',
                    {'id':subCategory.id}) }}">{{ subCategory.name }}</a>
            </li>
            {% endfor %}
    {% endfor %}
    <li><hr class="dropdown-divider"></li>
</ul>
</li>
```

> **Remarque :** observez le fichier « src\Entity\Category.php ». Chaque objet de type « Category » contient une liste « subCategories » que nous utilisons ici.

### 6. Mise à jour du menu pour la connexion utilisateurs.

Ouvrez le fichier « templates\layouts\nav.html.twig » et ajoutez le code suivant juste avant la partie « Recherche » :

```twig
{% if app.user == false %}
    <li class="nav-item">
        <a class="nav-link" href="{{ path('app_register') }}">Inscription</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ path('app_login') }}">Connexion</a>
    </li>
{% endif %}
```

> **TRAVAIL A FAIRE POUR LES ETUDIANTS :**
> - Ajouter la déconnexion (logout) dans la navBar.
