# Projet SIO-Shoes : site « e-commerce » avec Symfony

## Partie 17 : imprimer une facture en PDF

### 1. Création de la facture.

Créez le contrôleur « BillController » :

```bash
symfony console make:controller BillController
```

Ouvrez ensuite le fichier « src\Controller\BillController.php » et commencez par ajouter les bibliothèques « CategoryRepository » et « OrderRepository » :

```php
use App\Repository\OrderRepository;
use App\Repository\CategoryRepository;
```

Modifiez ensuite la route « app_bill » de la manière suivante :

```php
#[Route('/editor/order/{id}/bill', name: 'app_bill')]
public function index($id, OrderRepository $orderRepository, CategoryRepository $categoryRepository): Response
{
        $order = $orderRepository->find($id);

        return $this->render('bill/index.html.twig', [
           'categories' => $categoryRepository->findAll(),
           'order' =>$order
        ]);
}
```

### 2. Modifiez la vue « templates\bill\index.html.twig » pour afficher la facture en utilisant notre propre style.

> **Remarque :** n'hésitez pas à copier une partie du fichier « templates\order\orders.html.twig » :

```twig
<style>
    table{
        width: 100%;
        border-collapse: collapse;
    }
    th, td{
        border: 0.0625em solid #ddd;
    }
    thead{
        background:rgb(194, 194, 194);
    }
    tbody tr:nth-child(even){
        background:rgb(235, 235, 235);
    }
</style>
<div>
    <h2>Facture</h2>
    <hr>
    <h3>Commande n° {{ order.id }} du {{ order.createdAt|date("d/m/Y G:i:s") }}</h3>
    {% if order.isDelivered == true %}
        <h3 class="text-succes" >Commande livrée</h3>
    {% else %}
        <h3 class="text-danger" >Commande en attente de livraison</h3>
    {% endif %}
    <span>Informations client : </span>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>N°</th>
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
            <th>Nom produit</th>
            <th>Prix unitaire</th>
            <th>Quantité</th>
            <th>Prix Total</th>
        </tr>
    </thead>
    {% for product in order.orderProducts %}
            <tr>
                <td>{{ product.product.name }}</td>
                <td>{{ product.product.price }} €</td>
                <td>{{ product.qte }}</td>
                <td>{{ product.price * product.qte }} €</td>
            </tr>
    {% endfor %}
    </table>
    <h5>Frais de port : {{ order.shippingPrice }} €</h5>
     <hr>
    <h5>Total à payer : {{ order.totalPrice }} €</h5>
    <br>
</div>
```

Testez l'affichage en vous connectant avec le profil « Editor » et en utilisant l'URL suivante : « http://127.0.0.1:8000/editor/order/5/bill » (en changeant le numéro de la commande – ici la commande n° 5 est affichée).

### 3. Installer le composant « dompdf ».

Exécutez la commande suivante :

```bash
composer require dompdf/dompdf
```

Sortie de la commande (capture d'écran) :

```
PS C:\Symfony\e-commerce> composer require dompdf/dompdf
./composer.json has been updated
Running composer update dompdf/dompdf
Loading composer repositories with package information
Updating dependencies
Lock file operations: 4 installs, 0 updates, 0 removals
  - Locking dompdf/dompdf (v3.1.0)
  - Locking dompdf/php-font-lib (1.0.1)
  - Locking dompdf/php-svg-lib (1.0.0)
  - Locking sabberworm/php-css-parser (v8.8.0)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 4 installs, 0 updates, 0 removals
  - Downloading sabberworm/php-css-parser (v8.8.0)
  - Downloading dompdf/php-svg-lib (1.0.0)
  - Downloading dompdf/php-font-lib (1.0.1)
  - Downloading dompdf/dompdf (v3.1.0)
  - Installing sabberworm/php-css-parser (v8.8.0): Extracting archive
  - Installing dompdf/php-svg-lib (1.0.0): Extracting archive
  - Installing dompdf/php-font-lib (1.0.1): Extracting archive
  - Installing dompdf/dompdf (v3.1.0): Extracting archive
Generating autoload files
114 packages you are using are looking for funding.
Use the `composer fund` command to find out more!

Run composer recipes at any time to see the status of your Symfony recipes.

Executing script cache:clear [OK]
Executing script assets:install public [OK]
Executing script importmap:install [OK]

No security vulnerability advisories found.
Using version ^3.1 for dompdf/dompdf
```

### 4. Ouvrez le contrôleur « src\Controller\BillController.php » et ajoutez les bibliothèques « Dompdf\Dompdf » et « Dompdf\Options » :

```php
use Dompdf\Dompdf;
use Dompdf\Options;
```

Modifiez ensuite le code de la route « app_bill » :

```php
#[Route('/editor/order/{id}/bill', name: 'app_bill')]
public function index($id, OrderRepository $orderRepository): Response
{
        $order = $orderRepository->find($id);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont','Arial');

        $domPdf = new Dompdf($pdfOptions);

        $html = $this->renderView('bill/index.html.twig', [
           'order' =>$order,
        ]);

        $domPdf->loadHtml($html);
        $domPdf->render();
        $domPdf->stream('facture-'.$order->getId().'.pdf',[
            'Attachment'=>false //affichage dans le navigateur avant téléchargement
        ]);

        return new Response('',200,[
            'Content-Type'=> 'application/pdf',
        ]);
}
```

> **Remarque :** n'hésitez pas à consulter la documentation de Dompdf sur le site https://github.com/dompdf/dompdf

### 5. Ajout du bouton « Imprimer Facture ».

Modifiez la vue « templates\order\orders.html.twig » (juste avant le bouton de suppression de la commande) :

```twig
…
<h5>Frais de port : {{ order.shippingPrice }} €</h5>
<h5>Total à payer : {{ order.totalPrice }} €</h5>
<br>

{% if order.isDelivered != true %}
<a class="btn btn-outline-primary" href="{{ path('app_order_is_delivered_update', {'id': order.id}) }}" >Marquer comme livrée</a>
{% endif %}

<a class="btn btn-outline-success" href="{{ path('app_bill', {'id': order.id}) }}" target="_blank">Imprimer facture</a>

<br>
<br>
{{ include('order/_delete_form.html.twig') }}
<hr>
…
```

Testez le résultat :

> **Capture d'écran :** navigateur sur `127.0.0.1:8000/editor/order/5/bill` affichant le PDF de la facture : titre « Facture », « Commande n° 5 du 22/06/2025 14:39:37 », « Commande livrée », tableau « Informations client » (N° 5, JEROME, DUBROMELLE, 1 Rue neuve, 80000 Amiens, 0666666666), tableau « Produits commandés » :
>
> | Nom produit | Prix unitaire | Quantité | Prix Total |
> |---|---|---|---|
> | ADIDAS Men Adi Stars Tread Yellow Flip Flops | 26.30 € | 1 | 26.3 € |
> | ADIDAS Men Adi Quest Blue Sports Shoes | 56.40 € | 2 | 112.8 € |
>
> Puis « Frais de port : 4.50 € » et « Total à payer : 143.60 € ».
