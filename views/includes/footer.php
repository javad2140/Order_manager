        </div><!-- /container -->

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">طراحی و توسعه توسط شهداد خرداد © 1404</span>
        </div>
    </footer>

    <!-- All JavaScripts should be loaded here at the end of the body -->

    <!-- jQuery (required by Select2 and potentially helpful for other scripts) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    <!-- Bootstrap Bundle JS (includes Popper, does NOT need jQuery for Bootstrap components) -->
    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Jalali Datepicker JS -->
    <script src="<?php echo BASE_URL; ?>jalalidatepicker.min.js"></script>
    <script>
        // Debugging: Check if jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error("jQuery is NOT loaded!");
        } else {
            console.log("jQuery is loaded.");
        }

        // Debugging: Check if Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.error("Bootstrap JavaScript is NOT loaded!");
        } else {
            console.log("Bootstrap JavaScript is loaded.");
        }

        // Debugging: Check if Jalali Datepicker is loaded
        if (typeof jalaliDatepicker === 'undefined') {
            console.error("Jalali Datepicker JavaScript is NOT loaded!");
        } else {
            console.log("Jalali Datepicker JavaScript is loaded.");
            // Initialize Jalali Datepicker for all inputs with data-jdp attribute
            // FIX: Set a higher zIndex to ensure it appears above Bootstrap modals.
            jalaliDatepicker.startWatch({
                zIndex: 2000 // Set z-index higher than Bootstrap modals (default modal z-index is 1055)
            });
            console.log("Jalali Datepicker startWatch() called.");

            // Debugging: Check if any elements with data-jdp exist
            const jdpInputs = document.querySelectorAll('input[data-jdp]');
            if (jdpInputs.length > 0) {
                console.log(`Found ${jdpInputs.length} input(s) with data-jdp attribute.`);
                jdpInputs.forEach((input, index) => {
                    console.log(`Input ${index}: ID = ${input.id}, Value = ${input.value}`);
                });
            } else {
                console.warn("No input elements with data-jdp attribute found.");
            }
        }
    </script>
    <!-- Input Formatter JS (your custom module - load after other libraries if it depends on them) -->
    <script type="module" src="<?php echo BASE_URL; ?>assets/js/input-formatter.js"></script>
</body>
</html>
