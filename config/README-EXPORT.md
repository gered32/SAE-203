# 📦 Export de la base de données

Ce dossier contient des scripts pour exporter automatiquement votre base de données MySQL vers le fichier `database.sql`.

## 🎯 Pourquoi exporter la base de données ?

Le fichier `database.sql` sert à :
- 📦 **Initialiser** la base de données pour quelqu'un qui clone le projet
- 🔄 **Partager** la structure avec votre équipe
- 💾 **Versionner** la structure de votre BDD dans Git

**⚠️ Important :** Quand vous modifiez directement la base de données via phpMyAdmin, le fichier `database.sql` n'est **pas** automatiquement mis à jour. C'est pourquoi vous devez utiliser ces scripts pour synchroniser.

---

## 🚀 Méthode 1 : Script Batch (.bat) - RECOMMANDÉ

### Utilisation

1. **Double-cliquez** sur `export-database.bat`
2. Le script exporte automatiquement votre base
3. Vous verrez un message de confirmation

### Prérequis

- ✅ XAMPP doit être démarré
- ✅ MySQL doit être actif
- ✅ La base `sae203_ellusion` doit exister

### Ce que fait le script

- Vérifie la connexion MySQL
- Exporte toute la base de données
- Affiche des statistiques (nombre de tables, taille du fichier)
- Donne des instructions pour commit/push

---

## 🌐 Méthode 2 : Script PHP

### Utilisation via navigateur

Ouvrez dans votre navigateur :
```
http://localhost/SAE203_Niels/SAE-203/config/export-database.php
```

### Utilisation via terminal

```bash
cd D:\Xampp\htdocs\SAE203_Niels\SAE-203\config
php export-database.php
```

### Avantages

- ✅ Interface web stylée
- ✅ Fonctionne même si les scripts .bat sont bloqués
- ✅ Affiche des statistiques détaillées sur la base

---

## 📋 Workflow recommandé

### Quand exporter la base ?

Exportez votre base de données à chaque fois que vous :
- 🔧 Modifiez la structure (ajout/suppression de tables ou colonnes)
- 📝 Modifiez des données de référence (catégories, salles, œuvres, etc.)
- 🧪 Voulez partager votre base avec votre équipe

### Après l'export

1. **Vérifiez** le fichier `database.sql` généré
2. **Commitez** les modifications :
   ```bash
   git add config/database.sql
   git commit -m "Mise à jour de la base de données"
   git push origin main
   ```

---

## 🔧 Configuration

Les scripts utilisent les paramètres suivants :

| Paramètre | Valeur |
|-----------|--------|
| **Hôte MySQL** | `localhost` |
| **Utilisateur** | `root` |
| **Mot de passe** | *(vide par défaut)* |
| **Base de données** | `sae203_ellusion` |
| **Fichier de sortie** | `database.sql` |

Si votre configuration est différente, modifiez les scripts selon vos besoins.

---

## 🛠️ Options mysqldump utilisées

Les scripts utilisent les options suivantes pour générer un export de qualité :

| Option | Description |
|--------|-------------|
| `--add-drop-table` | Ajoute `DROP TABLE IF EXISTS` avant chaque `CREATE TABLE` |
| `--comments` | Ajoute des commentaires informatifs |
| `--dump-date` | Inclut la date de l'export |
| `--complete-insert` | Génère des INSERT complets avec noms de colonnes |
| `--skip-extended-insert` | Un INSERT par ligne (plus lisible) |
| `--default-character-set=utf8mb4` | Encodage UTF-8 pour les accents |
| `--routines` | Exporte les procédures stockées |
| `--triggers` | Exporte les triggers |
| `--events` | Exporte les événements planifiés |

---

## ❓ Dépannage

### Erreur : "Impossible de se connecter à MySQL"

**Solutions :**
1. Vérifiez que XAMPP est bien démarré
2. Vérifiez que le service MySQL est actif (voyant vert dans XAMPP Control Panel)
3. Testez la connexion via phpMyAdmin

### Erreur : "mysqldump.exe introuvable"

**Solutions :**
1. Vérifiez que XAMPP est installé dans `D:\Xampp`
2. Si XAMPP est ailleurs, modifiez le chemin dans les scripts

### Le fichier database.sql est vide

**Solutions :**
1. Vérifiez que la base `sae203_ellusion` existe
2. Vérifiez qu'elle contient des tables
3. Exécutez le script depuis un terminal pour voir les erreurs détaillées

---

## 📝 Note de sécurité

⚠️ **IMPORTANT :** Ces scripts sont destinés au développement local uniquement.

En production :
- 🔒 Protégez ces scripts avec un mot de passe
- 🚫 Ou supprimez-les complètement
- 🔐 Utilisez des sauvegardes automatiques sécurisées

---

## 📞 Support

Pour toute question ou problème :
- 📧 Contactez votre équipe de développement
- 📚 Consultez la documentation XAMPP
- 🐛 Créez une issue sur le dépôt Git

---

**Dernière mise à jour :** <?php echo date('d/m/Y'); ?>
