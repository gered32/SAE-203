    </main>
    <!-- ================================================================
         FIN DU CONTENU PRINCIPAL
         ================================================================ -->
    
    <!-- ================================================================
         PIED DE PAGE
         ================================================================ -->
    <footer class="site-footer">
        <div class="footer-container">
            <?php 
            // Affichage des réseaux sociaux uniquement sur la page d'accueil
            // La variable $pageAccueil doit être définie à true dans index.php
            if (isset($pageAccueil) && $pageAccueil === true): 
            ?>
            <!-- Réseaux sociaux (uniquement sur l'accueil) -->
            <div class="footer-social">
                <h3 class="footer-title">Suivez-nous</h3>
                <ul class="social-list">
                    <li>
                        <a href="https://www.instagram.com/mmichambery/" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           aria-label="Suivez MMI Chambéry sur Instagram (nouvelle fenêtre)">
                            <span class="social-icon" aria-hidden="true">
                                <!-- Icône Instagram en SVG inline (léger, pas de dépendance externe) -->
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </span>
                            <span class="social-text">Instagram</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.univ-smb.fr/" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           aria-label="Visitez le site MMI Chambéry (nouvelle fenêtre)">
                            <span class="social-icon" aria-hidden="true">
                                <!-- Icône Web/Globe en SVG inline -->
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                    <path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm7.931 9h-2.764a14.67 14.67 0 0 0-1.792-6.243A8.013 8.013 0 0 1 19.931 11zM12.53 4.027c1.035 1.364 2.427 3.78 2.627 6.973H8.843c.2-3.193 1.592-5.61 2.627-6.973.34-.45.66-.628 1.03-.628s.69.178 1.03.628zM8.625 4.757A14.67 14.67 0 0 0 6.833 11H4.069a8.013 8.013 0 0 1 4.556-6.243zM4.069 13h2.764a14.67 14.67 0 0 0 1.792 6.243A8.013 8.013 0 0 1 4.069 13zm7.401 6.973c-1.035-1.364-2.427-3.78-2.627-6.973h6.314c-.2 3.193-1.592 5.61-2.627 6.973-.34.45-.66.628-1.03.628s-.69-.178-1.03-.628zm3.905-.73A14.67 14.67 0 0 0 17.167 13h2.764a8.013 8.013 0 0 1-4.556 6.243z"/>
                                </svg>
                            </span>
                            <span class="social-text">Site de l'USMB</span>
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Informations légales -->
            <div class="footer-info">
                <p class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> E-LLUSION - Exposition Multimédia Interactive
                </p>
                <p class="footer-credits">
                    Projet réalisé par les étudiants du 
                    <a href="https://www.iut-acy.univ-smb.fr/mmi/" target="_blank" rel="noopener noreferrer">
                        BUT MMI Chambéry
                    </a>
                </p>
                <p class="footer-legal">
                    <a href="<?php echo SITE_URL; ?>/mentions-legales.php">Mentions légales</a>
                    <span class="separator" aria-hidden="true">|</span>
                    <a href="<?php echo SITE_URL; ?>/login.php">Espace admin</a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- ================================================================
         SCRIPTS JAVASCRIPT
         ================================================================ -->
    <!-- Script principal (carrousel, menu burger, etc.) -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php 
    // Inclusion de scripts supplémentaires si définis
    if (isset($scriptsSupplementaires) && is_array($scriptsSupplementaires)):
        foreach ($scriptsSupplementaires as $script):
    ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/<?php echo sanitize($script); ?>"></script>
    <?php 
        endforeach;
    endif; 
    ?>
</body>
</html>
