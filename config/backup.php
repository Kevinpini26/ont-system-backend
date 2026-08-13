<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disque de destination des sauvegardes
    |--------------------------------------------------------------------------
    |
    | "backups-local" par défaut (développement uniquement — voir
    | config/filesystems.php). Positionner BACKUP_DISK=backups-s3 avec les
    | identifiants BACKUP_S3_* avant tout déploiement réel : une sauvegarde
    | stockée sur le même serveur que l'application ne protège de rien en
    | cas de panne ou de compromission de ce serveur.
    |
    */
    'disk' => env('BACKUP_DISK', 'backups-local'),

    /*
    |--------------------------------------------------------------------------
    | Rétention
    |--------------------------------------------------------------------------
    |
    | Nombre de jours de sauvegardes conservées avant suppression
    | automatique par SauvegarderBaseDeDonneesCommand.
    |
    */
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),

];
