$(document).ready(function() {
    // SweetAlert untuk konfirmasi hapus
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', // red-500
            cancelButtonColor: '#6b7280', // gray-500
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.location.href = href;
            }
        });
    });

    // Preview gambar saat upload di form produk
    $('#gambar').on('change', function(event) {
        const [file] = this.files;
        if (file) {
            $('#imagePreview').attr('src', URL.createObjectURL(file)).removeClass('hidden');
            $('#iconPreview').addClass('hidden');
            $('#fileName').text(file.name);
        } else {
            $('#imagePreview').attr('src', '').addClass('hidden');
            $('#iconPreview').removeClass('hidden');
            $('#fileName').text('');
        }
    });

    // Ajax untuk update status pesanan di halaman daftar pesanan
    $('.status-select').on('change', function() {
        const order_id = $(this).data('id');
        const status = $(this).val();
        
        $.ajax({
            url: 'update_status.php', // Pastikan file ini ada di folder yang sama dengan halaman pesanan
            type: 'POST',
            data: {
                order_id: order_id,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                     Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message,
                    });
                }
            },
            error: function() {
                 Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Tidak dapat menghubungi server.',
                });
            }
        });
    });

    // Ajax untuk update stok di halaman Manajemen Stok
    $('.form-update-stok').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const productId = form.data('id');
        const newStock = form.find('input[name="stok"]').val();

        $.ajax({
            url: '../stok/proses_update_stok.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 1500, // Durasi pop-up
                        showConfirmButton: false
                    }).then(() => {
                        // INI BAGIAN PENTINGNYA:
                        // Setelah pop-up hilang, refresh halaman ke daftar stok utama
                        window.location.href = '/tokosepatu/modules/stok/stok.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server.'
                });
            }
        });
    });

}); // Akhir dari $(document).ready