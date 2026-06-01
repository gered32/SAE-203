# 07 - Accessibilité et Sobriété Numérique

## Objectifs pédagogiques

À la fin de ce chapitre, vous serez capable de :
- Expliquer les principes de base de l'accessibilité web (WCAG)
- Utiliser les balises HTML5 sémantiques correctement
- Implémenter des formulaires accessibles
- Justifier les choix d'éco-conception du projet

---

## 1. Accessibilité web (WCAG)

### 1.1 Qu'est-ce que l'accessibilité ?

L'**accessibilité web** signifie que les sites peuvent être utilisés par tous, y compris les personnes en situation de handicap :
- Déficience visuelle (non-voyants, malvoyants)
- Déficience auditive (sourds, malentendants)
- Déficience motrice (difficultés à utiliser une souris)
- Déficience cognitive (dyslexie, troubles de l'attention)

### 1.2 Les 4 principes WCAG 2.1

| Principe | Signification | Exemple |
|----------|---------------|---------|
| **Perceptible** | L'information doit être présentée de manière perceptible | Texte alternatif pour les images |
| **Utilisable** | L'interface doit être utilisable | Navigation au clavier |
| **Compréhensible** | Le contenu doit être compréhensible | Messages d'erreur clairs |
| **Robuste** | Le contenu doit être interprétable | HTML valide |

### 1.3 Niveaux de conformité

- **Niveau A** : exigences minimales
- **Niveau AA** : niveau recommandé (cible du projet)
- **Niveau AAA** : niveau optimal

---

## 2. HTML5 sémantique

### 2.1 Pourquoi la sémantique ?

Les balises sémantiques donnent du **sens** au contenu, pas seulement une apparence :

```html
<!-- ❌ Non sémantique -->
<div class="header">
    <div class="nav">...</div>
</div>
<div class="content">...</div>
<div class="footer">...</div>

<!-- ✅ Sémantique -->
<header>
    <nav>...</nav>
</header>
<main>...</main>
<footer>...</footer>
```

### 2.2 Balises utilisées dans le projet

| Balise | Usage | Fichier |
|--------|-------|---------|
| `<header>` | En-tête de page | header.php |
| `<nav>` | Navigation | header.php |
| `<main>` | Contenu principal | header.php |
| `<article>` | Contenu autonome | salles.php (cartes) |
| `<section>` | Section thématique | index.php |
| `<footer>` | Pied de page | footer.php |
| `<aside>` | Contenu secondaire | (optionnel) |

### 2.3 Exemple du header

```html
<!-- includes/header.php -->
<header class="site-header">
    <div class="header-container">
        <!-- Logo avec lien vers accueil -->
        <div class="site-logo">
            <a href="index.php" aria-label="Retour à l'accueil E-LLUSION">
                <span class="logo-text">E-LLUSION</span>
            </a>
        </div>
        
        <!-- Navigation avec aria-label -->
        <nav id="navigation-principale" aria-label="Navigation principale">
            <ul class="nav-list">
                <li><a href="index.php" class="nav-link">Accueil</a></li>
                <li><a href="salles.php" class="nav-link">Les Salles</a></li>
                <!-- ... -->
            </ul>
        </nav>
    </div>
</header>

<main id="contenu-principal" class="site-main">
    <!-- Contenu des pages -->
</main>
```

---

## 3. Attributs ARIA

### 3.1 Qu'est-ce qu'ARIA ?

**ARIA** (Accessible Rich Internet Applications) ajoute des informations pour les technologies d'assistance.

### 3.2 Attributs utilisés

| Attribut | Usage | Exemple |
|----------|-------|---------|
| `aria-label` | Étiquette invisible | `<nav aria-label="Navigation principale">` |
| `aria-expanded` | État ouvert/fermé | `<button aria-expanded="false">` |
| `aria-controls` | Élément contrôlé | `<button aria-controls="menu">` |
| `aria-current` | Page actuelle | `<a aria-current="page">` |
| `role` | Rôle de l'élément | `role="alert"` |

### 3.3 Exemple du menu burger

```html
<!-- Bouton burger -->
<button class="burger-menu" 
        aria-label="Ouvrir le menu de navigation" 
        aria-expanded="false"
        aria-controls="navigation-principale">
    <span class="burger-line"></span>
    <span class="burger-line"></span>
    <span class="burger-line"></span>
</button>
```

```javascript
// main.js - Mise à jour des attributs ARIA
burgerButton.addEventListener('click', function() {
    const isOpen = navigation.classList.toggle('nav-open');
    
    // Mettre à jour aria-expanded
    this.setAttribute('aria-expanded', isOpen);
    
    // Mettre à jour aria-label
    this.setAttribute('aria-label', 
        isOpen ? 'Fermer le menu' : 'Ouvrir le menu'
    );
});
```

---

## 4. Formulaires accessibles

### 4.1 Labels explicites

Chaque champ doit avoir un `<label>` associé :

```html
<!-- ✅ Correct : label avec for -->
<label for="email" class="form-label">Adresse email</label>
<input type="email" id="email" name="email" class="form-input">

<!-- ❌ Incorrect : pas de label -->
<input type="email" placeholder="Email">
```

### 4.2 Indication des champs requis

```html
<!-- CSS pour l'astérisque rouge -->
<label for="nom" class="form-label form-label-required">Nom</label>
<input type="text" id="nom" name="nom" required>
```

```css
.form-label-required::after {
    content: ' *';
    color: #af0000;  /* Rouge de la charte */
}
```

### 4.3 Messages d'erreur

```html
<!-- Associer l'erreur au champ avec aria-describedby -->
<div class="form-group">
    <label for="email">Email</label>
    <input type="email" 
           id="email" 
           name="email" 
           aria-describedby="email-error"
           aria-invalid="true"
           class="form-input error">
    <span id="email-error" class="form-error">
        L'adresse email n'est pas valide.
    </span>
</div>
```

### 4.4 Formulaire complet accessible

```html
<form method="POST" action="inscription.php">
    <fieldset>
        <legend>Vos informations</legend>
        
        <div class="form-group">
            <label for="nom" class="form-label form-label-required">
                Nom
            </label>
            <input type="text" 
                   id="nom" 
                   name="nom" 
                   class="form-input"
                   required
                   autocomplete="family-name"
                   maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="id_categorie" class="form-label form-label-required">
                Catégorie
            </label>
            <select id="id_categorie" name="id_categorie" required>
                <option value="">-- Choisir --</option>
                <option value="1">Enseignant·e</option>
                <!-- ... -->
            </select>
        </div>
    </fieldset>
    
    <button type="submit" class="btn btn-primary">
        Valider
    </button>
</form>
```

---

## 5. Navigation au clavier

### 5.1 Ordre de tabulation

L'ordre de tabulation doit suivre l'ordre visuel :

```html
<!-- L'ordre naturel du DOM = ordre de tabulation -->
<a href="index.php">Accueil</a>      <!-- Tab 1 -->
<a href="salles.php">Salles</a>      <!-- Tab 2 -->
<a href="inscription.php">Inscription</a>  <!-- Tab 3 -->
```

### 5.2 Focus visible

Le focus doit être **visible** pour les utilisateurs au clavier :

```css
/* Focus visible sur tous les éléments interactifs */
a:focus-visible,
button:focus-visible,
input:focus-visible,
select:focus-visible {
    outline: 3px solid var(--cyan);
    outline-offset: 2px;
}
```

### 5.3 Lien d'évitement

Permet de sauter directement au contenu :

```html
<!-- Tout en haut du body -->
<a href="#contenu-principal" class="skip-link">
    Aller au contenu principal
</a>

<!-- Plus bas dans la page -->
<main id="contenu-principal">
    <!-- ... -->
</main>
```

```css
.skip-link {
    position: absolute;
    top: -100px;  /* Caché par défaut */
    left: 50%;
    transform: translateX(-50%);
    /* ... */
}

.skip-link:focus {
    top: 10px;  /* Visible au focus */
}
```

---

## 6. Contrastes de couleurs

### 6.1 Ratios WCAG

| Niveau | Texte normal | Gros texte |
|--------|--------------|------------|
| AA | 4.5:1 | 3:1 |
| AAA | 7:1 | 4.5:1 |

### 6.2 Vérification des couleurs de la charte

| Combinaison | Ratio | Conforme AA ? |
|-------------|-------|---------------|
| Noir (#000) sur Cyan clair (#c5f9f2) | 15.5:1 | ✅ Oui |
| Noir (#000) sur Cyan (#3ce8d7) | 12.4:1 | ✅ Oui |
| Noir (#000) sur Blanc (#fff) | 21:1 | ✅ Oui |
| Blanc (#fff) sur Cyan foncé (#00bbaa) | 3.9:1 | ⚠️ Limite (gros texte OK) |
| Blanc (#fff) sur Rouge (#af0000) | 5.4:1 | ✅ Oui |

### 6.3 Outils de vérification

- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- Extension navigateur "WAVE"
- Outil développeur Chrome > Rendering > Emulate vision deficiencies

---

## 7. Sobriété numérique

### 7.1 Qu'est-ce que l'éco-conception ?

L'**éco-conception web** vise à réduire l'impact environnemental des sites :
- Moins de données transférées
- Moins de ressources serveur
- Appareils moins sollicités

### 7.2 Choix du projet

| Choix | Justification |
|-------|---------------|
| Pas de framework CSS | Bootstrap = ~150 Ko, notre CSS < 20 Ko |
| Pas de jQuery | jQuery = ~90 Ko, notre JS < 10 Ko |
| CSS vanilla | Pas de préprocesseur = pas d'outillage lourd |
| Images optimisées | Compression, dimensions adaptées |
| Pas d'animation lourde | Économie CPU/GPU |
| Police locale | Pas de requête vers Google Fonts |

### 7.3 Comparaison

| Site type | Poids moyen | Notre site |
|-----------|-------------|------------|
| Site classique | 2-3 Mo | < 200 Ko |
| Avec Bootstrap + jQuery | 500 Ko (JS/CSS seuls) | < 30 Ko |

### 7.4 Bonnes pratiques appliquées

```html
<!-- Chargement différé des images -->
<img src="image.jpg" loading="lazy" alt="Description">

<!-- Police avec font-display: swap -->
@font-face {
    font-family: 'CutePixel';
    src: url('../fonts/CutePixel.ttf');
    font-display: swap;  /* Affiche le fallback en attendant */
}
```

```css
/* Animations réduites si préférence utilisateur */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

---

## 8. Checklist accessibilité

### 8.1 Structure

- [x] Utilisation des balises sémantiques HTML5
- [x] Un seul `<h1>` par page
- [x] Hiérarchie des titres respectée (h1 > h2 > h3...)
- [x] Langue déclarée (`<html lang="fr">`)

### 8.2 Navigation

- [x] Lien d'évitement vers le contenu principal
- [x] Navigation au clavier fonctionnelle
- [x] Focus visible sur tous les éléments interactifs
- [x] Ordre de tabulation logique

### 8.3 Formulaires

- [x] Labels associés à tous les champs
- [x] Champs requis indiqués visuellement et dans le code
- [x] Messages d'erreur explicites
- [x] Autocomplétion activée (`autocomplete`)

### 8.4 Images et médias

- [x] Texte alternatif sur toutes les images informatives
- [x] Images décoratives avec `alt=""` ou en CSS
- [x] Pas d'information transmise uniquement par la couleur

### 8.5 Contrastes et lisibilité

- [x] Contraste suffisant texte/fond
- [x] Taille de police lisible (16px minimum)
- [x] Espacement suffisant entre les lignes

---

## 9. Points clés à retenir pour l'oral

1. **WCAG 2.1 AA** = niveau de conformité visé (légalement obligatoire pour le public)
2. **Sémantique HTML5** = donner du sens au contenu, pas juste du style
3. **ARIA** = informations supplémentaires pour les technologies d'assistance
4. **Contraste 4.5:1** = ratio minimum pour le texte normal
5. **Éco-conception** = site léger, sans framework lourd, images optimisées

---

## 10. Questions fréquentes du jury

**Q : Pourquoi ne pas utiliser Bootstrap ?**
> R : Bootstrap ajoute ~150 Ko au site et contient beaucoup de CSS inutilisé. Notre approche vanilla est plus légère, plus personnalisable et plus pédagogique.

**Q : Comment vérifier l'accessibilité d'un site ?**
> R : Avec des outils comme WAVE, Lighthouse (Chrome), ou manuellement (navigation clavier, lecteur d'écran). On teste aussi les contrastes avec WebAIM.

**Q : Le site est-il utilisable sans JavaScript ?**
> R : Partiellement. Les formulaires fonctionnent (soumission POST), mais le carrousel et le chargement dynamique des créneaux nécessitent JS. C'est un compromis acceptable.

**Q : Pourquoi `aria-label` sur le logo ?**
> R : Le logo est un lien cliquable mais ne contient que le texte "E-LLUSION". L'`aria-label` précise l'action ("Retour à l'accueil") pour les utilisateurs de lecteurs d'écran.
