# Projet SIO-Shoes : site « e-commerce » avec Symfony

## Partie 10 : afficher les produits sur la page d’accueil

> **TRAVAIL A FAIRE POUR LES ETUDIANTS :**
>
> Mettre à jour la BDD avec les données du projet SIO-Shoes :
> - Insérez les catégories (category.sql).
> - Insérez les sous-catégories (sub_category.sql).
> - Insérez les produits (product.sql).
> - Insérez la table product_sub_category (product_sub_category.sql).
>
> Copiez les images dans le répertoire « public\uploads\images »

### 1. Afficher les produits sur la page d’accueil.

Ouvrez le contrôleur « src\Controller\HomeController.php » et commençez par importer « ProductRepository » :

```php
use App\Repository\ProductRepository;
```

Modifiez la fonction « index() » pour afficher tous les produits :

```php
#[Route('/', name: 'app_home',  methods: ['GET'])]
public function index(ProductRepository $productRepository): Response
{
    return $this->render('home/index.html.twig', [
    'products' => $productRepository->findBy([],['name'=>"ASC"]),
    ]);
}
```

### 2. Créer une nouvelle route pour afficher le détail d’un produit.

Toujours dans le contrôleur « src\Controller\HomeController.php », importez l’entité « Product » :

```php
use App\Entity\Product;
```

Ajoutez le code suivant :

```php
#[Route('/home/product/{id}/show', name: 'app_home_product_show',  methods: ['GET'])]
public function show(Product $product): Response
{
        return $this->render('home/show.html.twig', [
            'product' => $product
        ]);
}
```

### 3. Afficher les produits sur la page d’accueil.

Modifiez la vue « templates\home\index.html.twig ».
Commencez par utiliser Bootstrap pour gérérer un système de cartes : allez dans la documentation puis dans « Components » et « Card ». Choisissez un exemple et copiez le code pour l’insérer dans la vue.
N’oubliez pas d’ajouter un lien (balise « <href>) pour ouvrir le détail des produits : on utilisera la route « 'app_home_product_show » créée plus haut.

Notez qu’une image par défaut est affichée s’il n’y pas d’image enregistrée dans la BDD pour un produit (ici « no_image_available_500x350.png »).

```twig
{% extends 'base.html.twig' %}
{% block title %}Home{% endblock %}
{% block body %}
<div class="container">
<br>
    <h1>Produits</h1>
    <div class="row">
        {% for product in products %}
            <div class="col-md-3 mt-4">
                <div class="card" style="width: 20vh:">
                    {% if product.image %}
                        <a href="{{ path('app_home_product_show', {'id': product.id})
                        }}"><img src="{{ asset('uploads/images/' ~ product.image) }}"
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

### 4. Ajoutez un lien vers la page d’accueil dans le menu.

Modifiez le fichier « templates\layouts\nav.html.twig » pour ajouter la route « /app_home » :

```twig
<div class="container">
    <a class="navbar-brand" href="{{ path('app_home') }}">My Shop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
    data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false"
    aria label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarScroll">
        <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll"
        style="--bs-scroll-height: 100px;">
        <li class="nav-item">
                <a class="nav-link active" aria-current="page"
                href="{{ path('app_home') }}">Home</a>
        </li>
        <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
        </li>
    …
```

### 5. Créer la vue « templates\home\show.html.twig » pour afficher le détail d’un produit :

```twig
{% extends 'base.html.twig' %}
{% block title %}Home{% endblock %}
{% block body %}
<div class="container">
<div class="card mb-12" style="margin-top: 3vh">
        <div class="row g-0">
                <div class="col-md-4">
                    {% if product.image %}
                        <img src="{{ asset('uploads/images/' ~ product.image) }}"
                        class="card-img-top" alt="{{ product.name }}">
                    {% else %}
                        <img src="{{ asset ('uploads/images/no_image_available_500x350.png')
                        }}" class="card-img-top" alt="Placeholder image">
                    {% endif %}
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h1 class="card-title">{{ product.name }}</h1>
                        <p class="card-text text-truncate">{{ product.description }}</p>
                        <h3>{{ product.price}} €</h3>
                        <a href="#" class="btn btn-outline-success">Ajouter au panier</a>
                        <br><br>
                        <a class="btn btn-dark" href="{{ path('app_home') }}">Retour</a>
                    </div>
                </div>
            </div>
    </div>
</div>
{% endblock %}
```

### 6. Afficher les derniers produits ajoutés dans la vue « templates\home\show.html.twig ».

Commencez par modifier la fonction « show() » dans le contrôleur « src\Controller\HomeController.php » :

```php
#[Route('/home/product/{id}/show', name: 'app_home_product_show',  methods: ['GET'])]
public function show(Product $product, ProductRepository $productRepository): Response
{
        $lastProducts = $productRepository->findBy([],['id'=>'DESC'],limit: 5);

        return $this->render('home/show.html.twig', [
            'product' => $product,
            'products' => $lastProducts
        ]);
}
```

### 7. Compléter la forme la vue « templates\home\show.html.twig » pour afficher les 5 derniers produits ajoutés à la BDD :

```twig
% extends 'base.html.twig' %}

{% block title %}Home{% endblock %}

{% block body %}
<div class="container">
    <div class="card mb-12" style="margin-top: 3vh">
        <div class="row g-0">
                <div class="col-md-4">
                    {% if product.image %}
                        <img src="{{ asset('uploads/images/' ~ product.image) }}"
                        class="card-img-top" alt="{{ product.name }}">
                    {% else %}
                        <img src="{{ asset
                        ('uploads/images/no_image_available_500x350.png') }}"
                        class="card-img-top" alt="Placeholder image">
                    {% endif %}
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                     <h1 class="card-title">{{ product.name }}</h1>
                        <p class="card-text text-truncate">{{ product.description }}</p>
                        <h3>{{ product.price}} €</h3>
                     <a href="#" class="btn btn-outline-success">Ajouter au panier</a>
                     <br><br>
                     <a class="btn btn-dark" href="{{ path('app_home') }}">Retour</a>
                    </div>
                </div>
            </div>
    </div>

    <br>
    <br>
    <h2>Derniers produits ajoutés</h2>
    <div class="row">
        {% for product in products %}
            <div class="col-md-3 mt-4">
                <div class="card" style="width: 20vh;">
                    {% if product.image %}
                            <a href="{{ path('app_home_product_show', {'id': product.id})
                            }}"><img src="{{ asset('uploads/images/' ~ product.image) }}"
                            class="card-img-top" alt="{{ product.name }}"> </a>
                    {% else %}
                            <img src="{{ asset ('uploads/images/no_image_available_500x350.png')
                            }}" class="card-img-top" alt="Placeholder image">
                    {% endif %}
                    <div class="card-body">
                            <a style="text-decoration: none" href="{{ path(
                            'app_home_product_show', {'id': product.id}) }}">
                            <h5 class="card-title">{{product.name}}</h5> </a>
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
