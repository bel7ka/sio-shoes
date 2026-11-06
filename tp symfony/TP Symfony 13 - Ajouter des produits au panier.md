# Projet SIO-Shoes : site « e-commerce » avec Symfony

## Partie 13 : ajouter des produits au panier

### 1. Ajouter un produit au panier

Créez le contrôleur pour le panier (« Cart » en Anglais) :

```bash
symfony console make:controller CartController
```

Ouvrez ensuite le fichier « src\Controller\CartController.php » et commencez par ajouter « SessionInterface » et « ProductRepository » :

```php
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductRepository;
```

On ajoute ensuite un constructeur à la classe puis on modifie le code de la manière suivante :

```php
final class CartController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepository){
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, CategoryRepository $categoryRepository):
    Response
    {
        // On récupère le panier à partir de la session 'cart'
        // (on crée un tableau vide par défaut)
        $cart = $session->get('cart', []);
        $cartWithData = [];

        // On boucle dans le tableau $cart et on récupère les données de chaque produit
        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);

            // On récupère les données du produit à partir de son id
            if ($product) {
                // On ajoute le produit et la quantité au tableau $cartWithData
                $cartWithData[] = [
                'product' => $product,
                'quantity' => $quantity
                ];
            }
        }

        // Calcul du prix total du panier
        $total = array_sum(array_map(function ($item) {
            return $item['product']->getPrice() * $item['quantity'];
        }, $cartWithData));

        return $this->render('cart/index.html.twig', [
        'items' => $cartWithData,
        'total' => $total,
        'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/cart/add/{id}/', name: 'app_cart_new', methods: ['GET'])]
    public function AddToCard(int $id, SessionInterface $session): Response
    {
        // On récupère la panier à partir de la session 'cart'
        $cart = $session->get('cart', []);

        // On met à jour la quantité si le produit est déjà présent dans le panier.
        // Sinon on ajoute le produit au panier.
        if (isset($cart[$id])) {
            $cart[$id]++;
        }
        else
        {
            $cart[$id] = 1;
        }

        // On enregistre la session
        $session->set('cart', $cart);

        // Redirection vers l'affichage du panier
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['GET'])]
    public function removeFromCart(int $id, SessionInterface $session): Response
    {
        // On récupère la panier à partir de la session 'cart'
        $cart = $session->get('cart', []);

        // On vérifie que la produit existe dans le panier et on le supprime
        if (isset($cart[$id])) {
            unset($cart[$id]); // Suppression du produit à partir de l'id
            $this->addFlash('danger','Le produit a été supprimé du panier');
        }

        // mise à jour de la variable de session
        $session->set('cart', $cart);

        // Redirection vers l'affichage du panier
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_cart_clear', methods: ['GET'])]
    public function clearCart(SessionInterface $session):Response
    {
        // Suppression de la variable de session
        $session->remove('cart');

        // Flash message pour informer l'utilisateur que la panier a été effacé
        $this->addFlash('success', 'Votre panier a été effacé.');

        // Redirection vers l'affichage du panier
        return $this->redirectToRoute('app_cart');
    }
}
```

### 2. Création de la vue

Ouvrez le fichier « templates\cart\index.html.twig » et ajoutez le code suivant :

```twig
{% extends 'base.html.twig' %}

{% block title %}Your Cart{% endblock %}

{% block body %}
    <div class="container">
        <br>
<h1>Votre panier</h1>
{% include 'layouts/_flash_message.html.twig' %}
        <br>
        {% if items is empty %}
            <p>Votre panier est vide.</p>
        {% else %}
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom du produit</th>
                        <th>Quantité</th>
                        <th>Prix</th>
                        <th>Total</th>
                        <th>Actions</th> <!-- Pour retirer du panier -->
                    </tr>
                </thead>
                <tbody>
                    {% for item in items %}
                        <tr>
                         <td>{{ item.product.name }}</td>
                         <td>{{ item.quantity }}</td>
                         <td>
                         {{ item.product.price|number_format(2, '.', ',') }} €
                         </td>
                         <td>
                         {{ (item.product.price * item.quantity)|number_format(
                         2, '.', ',') }} €
                         </td>
                         <td>
                                <!-- Bouton pour supprimer un produit du panier -->
                                <a href="{{ path('app_cart_remove', {'id': item.product.id})
                                }}" class="btn btn-danger btn-sm">Supprimer</a>
                         </td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                <h4>Total : {{ total|number_format(2, '.', ',') }} €</h4>
            </div>

            <div class="d-flex justify-content-end mt-3">
                    <a href="" class="btn btn-primary">Valider et Payer</a>
                    &nbsp
                    <a href="{{ path('app_cart_clear')}}" class="btn btn-danger">
                    Vider le panier</a>
            </div>
        {% endif %}
    </div>
{% endblock %}
```

### 3. Augmentation et diminution de la quantité d'un produit dans le panier

Commencez par ajouter une nouvelle route « app_cart_subtract » dans le contrôleur « src\Controller\CartController.php » :

```php
#[Route('/cart/subtract/{id}/', name: 'app_cart_subtract', methods: ['GET'])]
public function SubtractToCard(int $id, SessionInterface $session): Response
{
    // On récupère la panier à partir de la session 'cart'
    $cart = $session->get('cart', []);

    // Si la quantité à diminuer est supérieure à 1, on décrémente.
    // Sinon on retire le produit du panier
    if (isset($cart[$id]))
    {
            if ($cart[$id] > 1)
            {
                $cart[$id]--;
             }
            else
            {
                unset($cart[$id]); // Suppression du produit à partir de l'id
             }
    }

    // On enregistre la session
    $session->set('cart', $cart);

    // Redirection vers l'affichage du panier
    return $this->redirectToRoute('app_cart');
}
```

Modifiez ensuite la vue « templates\cart\index.html.twig » pour ajouter les liens « + » et « - » :

```twig
…
{% for item in items %}
<tr>
<td>{{ item.product.name }}</td>
            <td>{{ item.quantity }}
                  <a href="{{path('app_cart_new',{'id': item.product.id})}}"
                  class="btn btn-success btn-sm">+</a>
                  <a href="{{path('app_cart_subtract',{'id': item.product.id})}}"
                  class="btn btn-danger btn-sm">-</a>
            </td>
…
```

### 4. Ajout des liens pour l'ajout au panier lors de l'affichage des produits

Ouvrez les fichiers « templates\home\index.html.twig », « templates\home\show.html.twig » et « templates\home\filter.html.twig » puis modifiez les liens comme ci-dessous :

```twig
<a href="{{path('app_cart_new',{'id': product.id})}}" class="btn btn-outline-success">
Ajouter au panier</a>
```

### 5. Ajout de l'accès au panier dans la navbar

Ouvrez le fichier « templates\layouts\nav.html.twig » et ajoutez le code suivant à l'endroit où vous voulez faire apparaître le lien :

```twig
<li class="nav-item">
    <a class="nav-link" href="{{ path('app_cart') }}">Panier</a>
</li>
```
