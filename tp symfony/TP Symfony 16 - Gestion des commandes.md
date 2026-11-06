# Projet SIO-Shoes : site « e-commerce » avec Symfony

## Partie 16 : gestion des commandes

### 1. Créer la liste des commandes.

Ajoutez la route « app_orders » au contrôleur « src\Controller\OrderController.php » (attention, cette route est réservée au rôle « editor ») :

```php
#[Route('/editor/orders', name: 'app_orders')]
public function getAllOrders(OrderRepository $orderRepository, CategoryRepository $categoryRepository): Response
{
        $orders = $orderRepository->findAll();
        return $this->render('order/orders.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'orders' =>$orders
        ]);
}
```

### 2. Afficher la liste des commandes.

Créez le fichier « templates\order\orders.html.twig » et ajoutez le code suivant :

```twig
{% extends 'base.html.twig' %}

{% block title %}Commandes{% endblock %}

{% block body %}
   <div class = "container">
        <br>
        <h1>Commandes</h1>
            {% include 'layouts/_flash_message.html.twig' %}
        <br>
         {% for order in orders %}
             <h3>
             Commande n° {{ order.id }} du {{ order.createdAt|date("d/m/Y G:i:s") }}
             </h3>
             <span>Informations client : </span>
             <table class="table table-bordered">
                  <thead>
                      <tr>
                          <th>Id</th>
                          <th>Nom</th>
                          <th>Prénom</th>
                          <th>Adresse</th>
                          <th>Ville</th>
                          <th>Téléphone</th>
                      </tr>
                  </thead>

                  <tbody>
                      <tr>
                          <td>{{ order.id }}</td>
                          <td>{{ order.firstName }}</td>
                          <td>{{ order.lastName }}</td>
                          <td>{{ order.adress }}</td>
                          <td>{{ order.city.zipCode }} {{ order.city.name }}</td>
                          <td>{{ order.phoneNumber }}</td>
                      </tr>
                  </tbody>
            </table>
            <br>
            <span>Produits commandés : </span>
            <table class="table table-bordered" >
            <thead>
                   <tr>
                          <th>Image produit</th>
                          <th>Nom produit</th>
                          <th>Prix unitaire</th>
                          <th>Quantité</th>
                          <th>Prix Total</th>
                    </tr>
            </thead>
            {% for product in order.orderProducts %}
                   <tbody>
                          <tr>
                                    <td>
                                    <img style="width:100%; max-width:80px; height:auto;"
                                    src="{{asset('Uploads/images/'~product.product.image)}}">
                                    <td>
                                    <td>{{ product.product.name }}</td>
                                    <td>{{ product.product.price }} €</td>
                                    <td>{{ product.qte }}</td>
                                    <td>{{ product.price * product.qte }} €</td>
                          </tr>
                    </tbody>
            {% endfor %}
            </table>
            <h5>Frais de port : {{ order.shippingPrice }} €</h5>
            <h5>Total à payer : {{ order.totalPrice }} €</h5>
            <br>
            <hr>
            {% else %}
                        <h2>Aucune commande à afficher</h2>
                        <hr>
                    {% endfor %}
              <br>
              <a class="btn btn-dark" href="{{ path('app_home') }}">Retour</a>
        </div>
{% endblock %}
```

### 3. Ajout de l'accès à la liste des commandes dans la navbar (uniquement pour les administrateurs et les éditeurs).

Modifiez le code du fichier « templates\layouts\nav.html.twig » :

```twig
{% if is_granted("ROLE_EDITOR") %}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button"
    data-bs-toggle="dropdown" aria-expanded="false">Editeur</a>
    <ul class="dropdown-menu">
            <li>
                    <a class="dropdown-item" href="{{ path('app_product_index') }}">
                    Produits</a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                    <a class="dropdown-item" href="{{ path('app_city_index') }}">
                    Villes</a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ path('app_orders') }}">Commandes</a>
            </li>
    </ul>
</li>
{% endif %}
```

> **TRAVAIL A FAIRE POUR LES ETUDIANTS :**
> - Ajouter la possibilité de supprimer une commande dans la liste des commandes accessible au rôle « editor ».

### 4. Ajouter la possibilité de marquer une commande comme livrée dans la liste des commandes.

Modifiez l'entité « Order » en exécutant la commande `symfony console make:entity Order`

Ajoutez un champ « isDelivered » de type « boolean » (NULL possible) :

```
PS C:\Symfony\e-commerce> symfony console make:entity Order
 Your entity already exists! So let's add some new fields!

 New property name (press <return> to stop adding fields):
 > isDelivered

 Field type (enter ? to see all types) [boolean]:
 >

 Can this field be null in the database (nullable) (yes/no) [no]:
 > yes

 updated: src/Entity/Order.php

 Add another property? Enter the property name (or press <return> to stop adding fields):
 >


  Success!

 Next: When you're ready, create a migration with symfony.exe console make:migration
```

> **Remarque :** utiliser un nom de champ qui commence par « is » permet à symfony de détecter qu'il s'agit d'un type « bolean ».

### 5. Préparez et exécutez la migration pour mettre à jour la BDD :

```bash
symfony console make:migration
symfony console doctrine:migrations:migrate
```

### 6. Modifiez la vue « templates\order\orders.html.twig » pour préciser si chaque commande est livrée :

```twig
…
{% for order in orders %}
<h3>Commande n° {{ order.id }} du {{ order.createdAt|date("d/m/Y G:i:s") }}</h3>
    {% if order.isDelivered == true %}
            <h3 class="text-succes" >Commande livrée</h3>
    {% else %}
            <h3 class="text-danger" >Commande en attente de livraison</h3>
    {% endif %}
…
```

### 7. Ajouter la possibilité de marquer la commande comme « livrée » dans la liste des commandes.

Ajoutez une nouvelle route « app_order_is_delivered_update» au contrôleur « src\Controller\OrderController.php » :

```php
#[Route('/editor/order/{id}/is-delivered/update', name: 'app_order_is_delivered_update')]
public function isDeliveredUpdate($id, OrderRepository $orderRepository,
EntityManagerInterface $entityManager): Response
{
        $order = $orderRepository->find($id);
        $order->setIsDelivered(true);
        $entityManager->flush();

        $this->addFlash('success','La commande a été marquée comme livrée.');

        return $this->redirectToRoute('app_orders', [], Response::HTTP_SEE_OTHER);
}
```

### 8. Ajout du bouton « Marquer comme livrée ».

Modifiez la vue « templates\order\orders.html.twig » juste avant le bouton de suppression de la commande :

```twig
…
<h5>Frais de port : {{ order.shippingPrice }} €</h5>
<h5>Total à payer : {{ order.totalPrice }} €</h5>
<br>
{% if order.isDelivered != true %}
    <a class="btn btn-outline-primary"
    href="{{ path('app_order_is_delivered_update', {'id': order.id}) }}">
    Marquer comme livrée</a>
{% endif %}
<br>
<br>
{{ include('order/_delete_form.html.twig') }}
<br>
…
```
