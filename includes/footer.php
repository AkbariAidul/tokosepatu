<script>
    $(document).ready(function() {
        // Logika untuk membuka/menutup akordeon
        $('.accordion-toggle').on('click', function() {
            const clickedSubmenu = $(this).next('.submenu');

            // Menutup semua submenu lain kecuali yang diklik
            $('.submenu').not(clickedSubmenu).slideUp('fast');
            $('.accordion-toggle').not($(this)).find('.fa-chevron-down').removeClass('rotate-180');

            // Membuka atau menutup submenu yang diklik
            clickedSubmenu.slideToggle('fast');
            $(this).find('.fa-chevron-down').toggleClass('rotate-180');
        });

        // Logika agar menu akordeon tetap terbuka jika salah satu anaknya aktif
        $('.submenu a').each(function() {
            if ($(this).hasClass('bg-orange-500')) { // Cek class 'aktif'
                $(this).closest('.submenu').show();
                $(this).closest('.submenu').prev('.accordion-toggle').addClass('text-white');
                $(this).closest('.submenu').prev('.accordion-toggle').find('.fa-chevron-down').addClass('rotate-180');
            }
        });
    });
    </script>
    </main>
    </div>
    
    <script src="/tokosepatu/assets/js/script.js"></script>
    
    <?php
    // Flash message menggunakan SweetAlert
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        // ===== KODE SWEETALERT DIPERBARUI DI SINI =====
        echo "<script>
            Swal.fire({
                icon: '{$message['type']}',
                title: '{$message['title']}',
                text: '{$message['text']}',
                // Opsi toast dan timer dihapus agar menjadi pop-up modal
                showConfirmButton: true // Menampilkan tombol 'OK'
            });
        </script>";
        // ===============================================
    }
    ?>

    </body>
</html>