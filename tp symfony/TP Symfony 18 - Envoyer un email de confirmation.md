# Projet SIO-Shoes : site « e-commerce » avec Symfony

## Partie 18 : Envoyer un mail de confirmation

### 1. Installer Smtp4dev

Allez sur le site https://github.com/rnwood/smtp4dev et téléchargez la version Windows x64 « Desktop » de Smtp4dev.
Décompressez l’archive et lancez le fichier exécutable « Rnwood.Smtp4dev.Desktop.exe » :

> Capture d'écran : fenêtre de Smtp4dev (version 3.8.7), « SMTP server listening on port 2525 », onglets Messages / Sessions, boîte « Default » vide (« No messages in mailbox 'Default' »).

Allez dans les paramètres choisissez **2525** comme port du serveur **SMTP**.

Par défaut**, l’adresse du serveur SMTP est « localhost »** et il n’y a pas d’identifiant et de mot de passe (tout est modifiable si vous le souhaitez).

### 2. Créer la vue contenant le message à envoyer au fomat HTML.

Créez un fichier « templates\mail\orderConfirm.html.twig » et ajoutez le code suivant (vous pouvez copier différents éléments de la vue « templates\bill\index.html.twig ») :

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
    <h2>Confirmation de commande</h2>
    <p>
    Bonjour {{ order.firstName }} {{ order.lastName }}.
    Votre commande est confirmée.
    </p>
    <h3>Commande n° {{ order.id }} du {{ order.createdAt|date("d/m/Y G:i:s") }}</h3>
    <h3>Produits commandés : </h3>
    <table class="table table-bordered">
        <thead align="left">
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
    <h3>Frais de port : {{ order.shippingPrice }} €</h3>
    <hr>
    <h3>Total à payer : {{ order.totalPrice }} €</h3>
</div>
```

### 3. Modifier le contrôleur « src\Controller\OrderController.php» pour transmettre à la vue les éléments de la commande :

Ouvrez le ficher « src\Controller\OrderController.php» et commencez par ajouter les bibliothèques suivantes :

```php
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
```

Ajoutez le constructeur pour notre objet « mailer » en début de classe :

```php
final class OrderController extends AbstractController
{
    public function __construct(private MailerInterface $mailer){}
…
```

Dans la fonction index, ajoutez la liste des produits de la commande dans l’objet « order » qui va être envoyé à la vue :

```php
foreach ($cart as $id => $quantity) {
    $product = $productRepository->find($id);//On récupère les données du produit avec l’id

    if ($product) {
            $orderProduct = new OrderProducts();
            $orderProduct->setOrder($order);
            $orderProduct->setProduct($product);
            $orderProduct->setQte($quantity);
            $orderProduct->setPrice($product->getPrice());

            //Ajout de la liste des produits à la variable $order
            $order->addOrderProduct($orderProduct);

            $entityManager->persist($orderProduct);
            $entityManager->flush();
    }
}
```

Ajoutez ensuite le code suivant juste avant l’envoi du message « order_message » :

```php
$session->remove('cart'); //On vide le panier

//Envoi d'un mail de confirmation
$html = $this->renderView('mail/orderConfirm.html.twig',[
    'order'=>$order
]);

$email = (new Email())
->from('sio-shoes@edouardgand.fr')
->to('jdubromelle@edouardgand.fr')
->subject('Confirmation de commande Sio-Shoes')
->html($html);

$this->mailer->send($email);

//Message final
return $this->redirectToRoute('order_message', [], Response::HTTP_SEE_OTHER);
```

### 4. Configurer « Mailer »

Ouvrez le fichier « .env » de votre projet et modifiez la constante « MAILER_DSN » :

```env
MAILER_DSN=smtp://:@localhost:2525
```

> **Remarque :** vous pouvez indiquer le nom d’un utilisateur et un mot de passe si nécessaire.
> La syntaxe est la suivante : `MAILER_DSN=smtp://login:motDePasse@localhost:2525`

### 5. Utiliser « php messenger » pour envoyer les mails

Pour envoyer des e-mails, nous allons utiliser le composant « Messenger ». Ce composant permet aux applications d'envoyer et de recevoir des messages vers ou depuis d'autres applications ou via des files d'attente de messages.

Exécutez la commande : `php bin/console messenger:consume async -vv`

```bash
php bin/console messenger:consume async -vv
```

Sortie du terminal :

```
PS C:\Symfony\e-commerce> php bin/console messenger:consume async -vv

[OK] Consuming messages from transport "async".

// The worker will automatically exit once it has received a stop signal via the messenger:stop-workers command.

// Quit the worker with CONTROL-C.
```

### 6. Testez l’envoi de mail à partir de l’application

Passez une nouvelle commande sur l’application et observez la fenêtre « php messenger » :

```
PS C:\Symfony\e-commerce> php bin/console messenger:consume async -vv

[OK] Consuming messages from transport "async".

// The worker will automatically exit once it has received a stop signal via the messenger:stop-workers command.

// Quit the worker with CONTROL-C.

18:19:45 INFO      [messenger] Received message Symfony\Component\Mailer\Messenger\SendEmailMessage ["class" => "Symfony\Component\Mailer\Messenger\SendEmailMessage"]
18:19:45 INFO      [messenger] Message Symfony\Component\Mailer\Messenger\SendEmailMessage handled by Symfony\Component\Mailer\Messenger\MessageHandler::__invoke ["class" => "Symfony\Component\Mailer\Messenger\SendEmailMessage","handler" => "Symfony\Component\Mailer\Messenger\MessageHandler::__invoke"]
18:19:45 INFO      [messenger] Symfony\Component\Mailer\Messenger\SendEmailMessage was handled successfully (acknowledging to transport). ["class" => "Symfony\Component\Mailer\Messenger\SendEmailMessage","message_id" => 62]
```

Ouvrez Smtp4dev Desktop pour voir le mail envoyé par l’application :

> Capture d'écran : Smtp4dev affiche le mail reçu le 27/07/2025 18:19:45, de sio-shoes@edouardgand.fr à jdubromelle@edouardgand.fr, sujet « Confirmation de commande Sio-Shoes ».
> Contenu : « Bonjour JEROME DUBROMELLE. Votre commande est confirmée. » ; « Commande n° 67 du 27/07/2025 18:19:43 » ; tableau des produits commandés :
>
> | Nom produit | Prix unitaire | Quantité | Prix Total |
> |---|---|---|---|
> | ADIDAS Black Hydroterra Shandal Casual Shoes | 95.70 € | 2 | 191.4 € |
> | ADIDAS Men Adi Cells Black Flip Flops | 72.40 € | 1 | 72.4 € |
>
> Frais de port : 5.50 € ; Total à payer : 269.3 €

### TRAVAIL A FAIRE POUR LES ETUDIANTS :

- Ajouter la saisie de l’adresse mail dans le formulaire de passation de la commande (attention, il faudra vérifier la validité de l’e-mail).
- Envoyer le mail à l’adresse saisie dans le formulaire.
