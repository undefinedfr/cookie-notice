# (Undefined) Cookie Notice

### Description

Plugin de conformité à la norme RGPD.

### Fonctionnalités
[1.0.0]
* Ajouter une barre de cookie avec un texte administrable
* Choisir une page WP contenant la politique de confidentialité du site
* Ajouter les cookies présents dans le site pour les afficher dans la page contenant la politique de confidentialité du site
* Acceptation des cookies par le visiteur: ajouts des scripts bloqués, autorisation de déposer des cookies
* Refus des cookies par le visiteur: Suppresion des cookies déjà posés. Blocage des prochains cookies.
* Choix de la durée de vie du cookie d'acceptation.
* Code JS à éécuter à l'acceptation des cookies
* Envoi d'un évent JS à l'acceptation
* Scripts à bloquer avant acceptation des cookies

[1.0.1]
* Autoriser certains cookies (supression au window.load)

[1.0.2]
* Possibilité de forcer l'acception des cookies au scroll

[1.0.3]
* Choisir le délai en secondes avant apparition de la barre de cookies

[1.1.0]
* Possibilité d'ajouter un titre
* Possibilité de traduire
* Possibilité de surcharger les templates de vues
* Possibilité de cacher la banniere au click en dehors de celle-ci

### Requis

* Module ACF PRO


### Constant

**`COOKIE_CONSENT`** **true** _si l'utisateur à accepter le cookies_ | **false** _autrement_


### Shortcode

**`[cookies_list]`** Le shortcode retourne un tableau des cookies remplis en BO (nom, fonction, type)


### JS Event(s)

**`'cookie:accepted'`** Cet événement est dispatch au moment de l'acceptation des cookies. Le cookie _hasConsent_ est également ajouté.


### Filters

**`undfnd_cookie_default_cookies_names`**

###### Definition:

Permet d'ajouter/editer/supprimer des noms de cookies depuis le code

###### Exemple:

```
function add_cookies_names( $cookies_names ) {
    $new_cookies_names = [
        'cookie_name' => '_nom_du_cookie'
    ];
    
    return array_merge($new_cookies_names, $cookies_names);
}
add_filter( 'undfnd_cookie_default_cookies_names', 'add_cookies_names', 10, 1 );
```


###### ___


**`undfnd_cookie_banned_scripts`**

###### Definition:

Permet d'ajouter/editer/supprimer des noms de cookies depuis le code

###### Exemple:

```
function add_cookies_banned_scripts( $banned_scripts ) {
    $new_banned_scripts = [
        'handle' => 'handle_du_script'
    ];
    
    return array_merge($new_banned_scripts, $banned_scripts);
}
add_filter( 'undfnd_cookie_banned_scripts', 'add_cookies_banned_scripts', 10, 1 );
```


###### ___


**`undfnd_label_cookie_accept`**

###### Definition:

Texte du bouton d'acceptation

###### Exemple:

```
function accept_cookie_label_button(  ) {
    return __('Ok');
}
add_filter( 'undfnd_label_cookie_accept', 'accept_cookie_label_button', 10, 1 );
```


###### ___


**`undfnd_label_cookie_decline`**

###### Definition

Texte du bouton _"En savoir plus ou s'opposer"_

###### Exemple

```
function decline_cookie_label_button(  ) {
    return __('En savoir plus');
}
add_filter( 'undfnd_label_cookie_decline', 'decline_cookie_label_button', 10, 1 );
```


###### ___


**`undfnd_label_cookie_name`**

###### Definition

Th de la colonne _"Nom du cookie"_

###### Exemple

```
function label_cookie_name() {
    return __('Nom');
}
add_filter( 'undfnd_label_cookie_name', 'label_cookie_name', 10, 1 );
```


###### ___


**`undfnd_label_cookie_function`**

###### Definition

Th de la colonne _"Fonction du cookie"_

###### Exemple

```
function label_cookie_function() {
    return __('Fonction');
}
add_filter( 'undfnd_label_cookie_function', 'label_cookie_function', 10, 1 );
```


###### ___


**`undfnd_label_cookie_state_text_accept`**

###### Definition

Texte d'état de consentement du dépôt de cookies _"Vous avez accepté les cookies"_

###### Exemple

```
function label_cookie_state_text_accept() {
    return __('Les cookies sont autorisés');
}
add_filter( 'undfnd_label_cookie_state_text_accept', 'label_cookie_state_text_accept', 10, 1 );
```


###### ___


**`undfnd_label_cookie_state_text_refuse`**

###### Definition

Texte d'état de consentement du dépôt de cookies _"Vous avez refusé les cookies"_

###### Exemple

```
function label_cookie_state_text_refuse() {
    return __('Les cookies sont non-autorisés');
}
add_filter( 'undfnd_label_cookie_state_text_refuse', 'label_cookie_state_text_refuse', 10, 1 );
```


###### ___

**`undfnd_label_cookie_authorize`** _[1.1.0]_

###### Definition

Th de la colonne _"✔"_

###### Exemple

```
function label_cookie_authorize() {
    return __('Autoriser ?');
}
add_filter( 'undfnd_label_cookie_authorize', 'label_cookie_authorize', 10, 1 );
```


