<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Mentions légales
 * ============================================================================
 * Page contenant les mentions légales obligatoires du site
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/includes/functions.php';

// Configuration de la page
$pageTitle = "Mentions légales";
$pageDescription = "Mentions légales du site E-LLUSION - Exposition multimédia interactive.";
$pageActive = "";

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<article class="mentions-legales">
    
    <h1>Mentions légales</h1>
    
    <section class="mentions-section">
        <h2>1. Éditeur du site</h2>
        <p>Le site E-LLUSION est édité dans le cadre d'un projet pédagogique universitaire :</p>
        <ul>
            <li><strong>Établissement :</strong> Université Savoie Mont Blanc (USMB)</li>
            <li><strong>Département :</strong> BUT Métiers du Multimédia et de l'Internet (MMI)</li>
            <li><strong>Adresse :</strong> IUT d'Annecy, 9 rue de l'Arc-en-Ciel, BP 240, 74942 Annecy-le-Vieux Cedex</li>
            <li><strong>Téléphone :</strong> +33 (0)4 50 09 22 22</li>
            <li><strong>Email :</strong> <?php echo sanitize(EMAIL_REFERENT); ?></li>
            <li><strong>Site web :</strong> <a href="https://www.univ-smb.fr" target="_blank" rel="noopener">www.univ-smb.fr</a></li>
        </ul>
        
        <p><strong>Responsable de publication :</strong> Référent du projet E-LLUSION</p>
    </section>
    
    <section class="mentions-section">
        <h2>2. Hébergement</h2>
        <p>Le site E-LLUSION est hébergé par :</p>
        <ul>
            <li><strong>Hébergeur :</strong> OVH</li>
            <li><strong>Société :</strong> OVH SAS</li>
            <li><strong>Adresse :</strong> 2 rue Kellermann, 59100 Roubaix, France</li>
            <li><strong>Téléphone :</strong> 1007 (numéro non surtaxé)</li>
            <li><strong>Site web :</strong> <a href="https://www.ovh.com" target="_blank" rel="noopener">www.ovh.com</a></li>
        </ul>
    </section>
    
    <section class="mentions-section">
        <h2>3. Propriété intellectuelle</h2>
        <p>L'ensemble des contenus présents sur le site E-LLUSION (textes, images, graphismes, logos, icônes, sons, logiciels, etc.) est protégé par le droit d'auteur et le droit de la propriété intellectuelle.</p>
        
        <p>Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable.</p>
        
        <p>Toute exploitation non autorisée du site ou de l'un quelconque des éléments qu'il contient sera considérée comme constitutive d'une contrefaçon et poursuivie conformément aux dispositions des articles L.335-2 et suivants du Code de Propriété Intellectuelle.</p>
    </section>
    
    <section class="mentions-section">
        <h2>4. Protection des données personnelles</h2>
        
        <h3>4.1. Responsable du traitement</h3>
        <p>Le responsable du traitement des données personnelles est l'Université Savoie Mont Blanc, représentée par son président.</p>
        
        <h3>4.2. Données collectées</h3>
        <p>Dans le cadre de l'inscription à l'exposition E-LLUSION, nous collectons les données personnelles suivantes :</p>
        <ul>
            <li>Nom et prénom</li>
            <li>Adresse email</li>
            <li>Catégorie de visiteur (enseignant, étudiant, personnel, etc.)</li>
            <li>Nombre de personnes accompagnantes</li>
            <li>Participation au buffet (optionnel)</li>
        </ul>
        
        <h3>4.3. Finalité du traitement</h3>
        <p>Les données collectées sont utilisées exclusivement pour :</p>
        <ul>
            <li>Gérer les inscriptions à l'exposition</li>
            <li>Organiser les créneaux de visite</li>
            <li>Vous envoyer une confirmation d'inscription par email</li>
            <li>Gérer la jauge maximale par créneau</li>
            <li>Organiser le buffet du jeudi</li>
        </ul>
        
        <h3>4.4. Base légale</h3>
        <p>Le traitement de vos données personnelles repose sur votre consentement, que vous donnez lors de votre inscription.</p>
        
        <h3>4.5. Durée de conservation</h3>
        <p>Vos données personnelles sont conservées uniquement pendant la durée nécessaire à l'organisation de l'exposition, soit jusqu'au 30 juin 2026, puis elles seront supprimées.</p>
        
        <h3>4.6. Vos droits</h3>
        <p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez des droits suivants :</p>
        <ul>
            <li><strong>Droit d'accès :</strong> Vous pouvez demander à consulter vos données personnelles</li>
            <li><strong>Droit de rectification :</strong> Vous pouvez demander la correction de vos données</li>
            <li><strong>Droit à l'effacement :</strong> Vous pouvez demander la suppression de vos données</li>
            <li><strong>Droit d'opposition :</strong> Vous pouvez vous opposer au traitement de vos données</li>
            <li><strong>Droit à la portabilité :</strong> Vous pouvez demander à récupérer vos données dans un format structuré</li>
        </ul>
        
        <p>Pour exercer ces droits, vous pouvez :</p>
        <ul>
            <li>Modifier ou supprimer votre inscription via le lien reçu par email</li>
            <li>Nous contacter à l'adresse : <?php echo sanitize(EMAIL_REFERENT); ?></li>
        </ul>
        
        <h3>4.7. Sécurité des données</h3>
        <p>Nous mettons en œuvre toutes les mesures techniques et organisationnelles appropriées pour protéger vos données personnelles contre la destruction, la perte, l'altération, la divulgation ou l'accès non autorisé.</p>
        
        <h3>4.8. Cookies</h3>
        <p>Le site E-LLUSION utilise uniquement des cookies de session strictement nécessaires au fonctionnement du site (authentification, gestion des inscriptions). Ces cookies ne sont pas utilisés à des fins publicitaires ou de traçage.</p>
        
        <h3>4.9. Droit de réclamation</h3>
        <p>Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une réclamation auprès de la Commission Nationale de l'Informatique et des Libertés (CNIL) :</p>
        <ul>
            <li><strong>Adresse :</strong> 3 Place de Fontenoy, TSA 80715, 75334 Paris Cedex 07</li>
            <li><strong>Téléphone :</strong> 01 53 73 22 22</li>
            <li><strong>Site web :</strong> <a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a></li>
        </ul>
    </section>
    
    <section class="mentions-section">
        <h2>5. Limitation de responsabilité</h2>
        
        <h3>5.1. Contenu du site</h3>
        <p>L'Université Savoie Mont Blanc s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur ce site. Toutefois, elle ne peut garantir l'exactitude, la précision ou l'exhaustivité des informations mises à disposition.</p>
        
        <h3>5.2. Disponibilité du site</h3>
        <p>L'Université Savoie Mont Blanc ne peut être tenue responsable des interruptions du site et des conséquences qui peuvent en découler pour l'utilisateur. Des interruptions peuvent survenir notamment en cas de maintenance, de mise à jour ou pour toute autre raison (panne technique, force majeure, etc.).</p>
        
        <h3>5.3. Liens hypertextes</h3>
        <p>Le site E-LLUSION peut contenir des liens hypertextes vers d'autres sites. L'Université Savoie Mont Blanc n'exerce aucun contrôle sur ces sites externes et ne saurait être tenue responsable de leur contenu.</p>
    </section>
    
    <section class="mentions-section">
        <h2>6. Droit applicable et juridiction compétente</h2>
        <p>Les présentes mentions légales sont régies par le droit français.</p>
        <p>En cas de litige et à défaut d'accord amiable, le litige sera porté devant les tribunaux français conformément aux règles de compétence en vigueur.</p>
    </section>
    
    <section class="mentions-section">
        <h2>7. Crédits</h2>
        <p>Ce site a été réalisé dans le cadre de la SAE203 du BUT Métiers du Multimédia et de l'Internet.</p>
        <ul>
            <li><strong>Conception et développement :</strong> Étudiants BUT MMI - Promotion 2025-2026</li>
            <li><strong>Établissement :</strong> IUT d'Annecy - Université Savoie Mont Blanc</li>
            <li><strong>Technologies utilisées :</strong> PHP, MySQL, HTML5, CSS3, JavaScript</li>
        </ul>
    </section>
    
    <section class="mentions-section">
        <h2>8. Contact</h2>
        <p>Pour toute question concernant le site E-LLUSION ou ces mentions légales, vous pouvez nous contacter :</p>
        <ul>
            <li><strong>Par email :</strong> <a href="mailto:<?php echo sanitize(EMAIL_REFERENT); ?>"><?php echo sanitize(EMAIL_REFERENT); ?></a></li>
            <li><strong>Via le formulaire de contact :</strong> <a href="contact.php">Page de contact</a></li>
        </ul>
    </section>
    
    <p class="mentions-footer">
        <em>Dernière mise à jour : <?php echo date('d/m/Y'); ?></em>
    </p>
    
</article>

<style>
    .mentions-legales {
        max-width: 900px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
        line-height: 1.8;
    }
    
    .mentions-legales h1 {
        text-align: center;
        margin-bottom: var(--spacing-xxl);
        color: var(--cyan);
    }
    
    .mentions-section {
        background: var(--blanc);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
        box-shadow: var(--shadow);
    }
    
    .mentions-section h2 {
        color: var(--cyan-fonce);
        border-bottom: 2px solid var(--cyan-clair);
        padding-bottom: var(--spacing-sm);
        margin-bottom: var(--spacing-lg);
    }
    
    .mentions-section h3 {
        color: var(--gris-fonce);
        margin-top: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
    }
    
    .mentions-section ul {
        margin: var(--spacing-md) 0;
        padding-left: var(--spacing-xl);
    }
    
    .mentions-section li {
        margin-bottom: var(--spacing-sm);
    }
    
    .mentions-section a {
        color: var(--cyan-fonce);
        text-decoration: underline;
    }
    
    .mentions-section a:hover {
        color: var(--cyan);
    }
    
    .mentions-footer {
        text-align: center;
        color: var(--gris);
        font-size: var(--font-size-small);
        margin-top: var(--spacing-xxl);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--gris-clair);
    }
    
    @media (max-width: 768px) {
        .mentions-legales {
            padding: var(--spacing-lg) var(--spacing-sm);
        }
        
        .mentions-section {
            padding: var(--spacing-lg);
        }
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
