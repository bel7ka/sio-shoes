# Projet SIO-Shoes : site « e-commerce » avec Symfony

## Partie 12 : mise en place de la pagination pour afficher les produits

### 1. Installation d'un bundle pour la gestion de la pagination

Nous allons utiliser le bundle « Knp Paginator » (n'hésitez pas à consulter la documentation sur https://github.com/KnpLabs/KnpPaginatorBundle). Commencez par installer le bundle avec la commande suivante :

```bash
composer require knplabs/knp-paginator-bundle
```

### 2. Ajouts dans le contrôleur « src\Controller\HomeController.php »

Commencez par importer la librairie « Request » :

```php
use Symfony\Component\HttpFoundation\Request;
```

Ajoutez ensuite la librairie « PaginationInterface » :

```php
use Knp\Component\Pager\PaginatorInterface;
```

Modifiez en suite la fonction « index » de la route « app_home » :

```php
#[Route('/', name: 'app_home',  methods: ['GET'])]
public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator ): Response
{
        $data =  $productRepository->findBy([],['name'=>"ASC"]);
        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1), /* page number */
            12 /* limit per page */
        );

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
        ]);
}
```

> **Note :** le PDF indique « PaginationInterface » dans le texte mais l'import affiché est `Knp\Component\Pager\PaginatorInterface` (lecture de la capture ; le signature de la fonction utilise `PaginatorInterface $paginator`).

### 3. Modifier la vue « templates\home\index.html.twig » pour afficher la liste des pages

Ajoutez le code suivant en fin de page (avant la fermeture de la dernière balise `</div>`) :

```twig
 <br>
    {{ knp_pagination_render(products,'layouts/pagination_template.html.twig') }}
</div>
{% endblock %}
```

### 4. Création d'un nouveau template pour l'affichage de la pagination

Créez un nouveau fichier « templates\layouts\pagination_template.html.twig » et ajoutez le code suivant :

```twig
{% if pageCount > 1 %}
    <nav aria-label="...">
    <ul class="pagination" style="display: flex; justify-content: space-evenly">

        {% if previous is defined %}
             <li class="page-item">
                    <a class="page-link" href="{{ path(route,
                    query|merge({(pageParameterName):first}))}}" >Début</a>
            </li>
            <li class="page-item">
            <a class="page-link" href="{{ path(route, query|merge(
            {(pageParameterName):previous}))}}" >Précédent</a>
            </li>
        {% endif %}
        {% for page in pagesInRange %}
            {% if page == current %}
                    <li class="page-item active" aria-current="page">
                            <a class="page-link" href="{{ path(route, query|merge(
                            {(pageParameterName):page}))}}">{{ page }}</a>
                    </li>
            {% else %}
                    <li class="page-item" aria-current="page">
                            <a class="page-link" href="{{ path(route, query|merge(
                            {(pageParameterName):page}))}}">{{ page }}</a>
                    </li>
            {% endif %}
        {% endfor %}

        {% if next is defined %}
            <li class="page-item">
                    <a class="page-link" href="{{ path(route, query|merge(
                    {(pageParameterName):next}))}}">Suivant</a>
            </li>
            <li class="page-item">
                    <a class="page-link" href="{{ path(route, query|merge(
                    {(pageParameterName):last}))}}">Fin</a>
            </li>
        {% endif %}
    </ul>
    </nav>
{% endif %}
```

### TRAVAIL À FAIRE POUR LES ÉTUDIANTS

- Mettre en place la pagination lors de l'utilisation des filtres (affichage des produits d'une sous-catégorie).
