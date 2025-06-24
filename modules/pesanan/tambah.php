<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }
$customers = $pdo->query("SELECT id, nama FROM customers ORDER BY nama ASC")->fetchAll();
$produks = $pdo->query("SELECT id, nama_produk, harga, stok, berat FROM produk WHERE stok > 0 ORDER BY nama_produk ASC")->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Tambah Pesanan Manual</h1>
    <p class="text-gray-500 mt-1">Buat pesanan baru atas nama customer yang sudah terdaftar.</p>
</div>

<div class="bg-white p-6 md:p-8 rounded-2xl shadow-lg">
    <form id="orderForm" action="proses_tambah.php" method="POST">
        <input type="hidden" name="kupon_id" id="kuponId">
        <input type="hidden" name="diskon" id="diskonAmount">

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
            <div class="lg:col-span-3 space-y-6">
                <div class="form-group">
                    <select id="customer_id" name="customer_id" class="form-input form-select" required><option value="" selected disabled>-- Pilih Customer --</option><?php foreach($customers as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option><?php endforeach; ?></select>
                    <label for="customer_id" class="form-label">Pilih Customer</label>
                </div>
                <hr>
                <h3 class="text-lg font-semibold text-gray-700">Item Produk</h3>
                <div id="produk-container" class="space-y-4">
                    </div>
                <button type="button" id="add-produk" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm"><i class="fas fa-plus"></i> Tambah Produk</button>
            </div>
            
            <div class="lg:col-span-2 bg-slate-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Ringkasan Pesanan</h3>
                <div class="mb-4">
                    <label for="kode_kupon" class="text-sm font-medium text-gray-700">Kode Kupon (Opsional)</label>
                    <div class="flex gap-2 mt-1">
                        <input type="text" id="kode_kupon" name="kode_kupon_input" class="w-full border-gray-300 rounded-lg shadow-sm uppercase" placeholder="Masukkan Kupon">
                        <button type="button" id="apply-kupon-btn" class="bg-gray-800 text-white font-semibold px-4 rounded-lg hover:bg-gray-900">Terapkan</button>
                    </div>
                    <div id="kupon-feedback" class="text-sm mt-2"></div>
                </div>
                <hr class="my-4">
                <div class="space-y-2 text-gray-700">
                    <div class="flex justify-between"><p>Subtotal Produk</p><p id="summary-subtotal" class="font-semibold">Rp 0</p></div>
                    <div class="flex justify-between"><p>Biaya Pengiriman (<span id="summary-berat">0</span> kg)</p><p id="summary-ongkir" class="font-semibold">Rp 0</p></div>
                    <div id="summary-diskon-row" class="flex justify-between text-green-600 hidden"><p>Diskon</p><p id="summary-diskon" class="font-semibold">- Rp 0</p></div>
                </div>
                <div class="mt-4 pt-4 border-t-2">
                    <div class="flex justify-between text-xl font-bold text-gray-900"><p>Grand Total</p><p id="summary-grandtotal">Rp 0</p></div>
                </div>
                <div class="form-group mt-6">
                    <select name="status" id="status" class="form-input form-select" required><option value="pending">Pending</option><option value="diproses">Diproses</option><option value="dikirim">Dikirim</option><option value="selesai">Selesai</option><option value="dibatalkan">Dibatalkan</option></select>
                    <label for="status" class="form-label">Status Pesanan</label>
                </div>
                <div class="pt-6"><button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-5 rounded-lg shadow-sm"><i class="fas fa-save mr-2"></i> Buat Pesanan</button></div>
            </div>
        </div>
    </form>
</div>

<div id="produk-template" style="display: none;">
    <div class="row produk-item grid grid-cols-12 gap-4 items-center border-t pt-4">
        <div class="col-span-12 md:col-span-7"><select name="produk_id[]" class="produk-select w-full border-gray-300 rounded-lg shadow-sm" required><option value="" selected disabled>-- Pilih Produk --</option><?php foreach ($produks as $p): ?><option value="<?= $p['id']; ?>" data-harga="<?= $p['harga']; ?>" data-berat="<?= $p['berat']; ?>"><?= htmlspecialchars($p['nama_produk']); ?></option><?php endforeach; ?></select></div>
        <div class="col-span-6 md:col-span-2"><input type="number" name="jumlah[]" class="jumlah-input w-full border-gray-300 rounded-lg shadow-sm" required min="1" value="1"></div>
        <div class="col-span-6 md:col-span-3"><button type="button" class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm remove-produk">Hapus</button></div>
    </div>
</div>

<script>
// Logika tambah hapus produk dan kalkulasi otomatis
$(document).ready(function() {
    // --- Variabel Global ---
    let tarif_per_kg = <?= $pdo->query("SELECT setting_value FROM pengaturan WHERE setting_key = 'biaya_pengiriman'")->fetchColumn() ?? 10000 ?>;
    let kupon = { id: null, diskon: 0 };

    // --- Fungsi Utama ---
    function calculateSummary() {
        let subtotal = 0;
        let totalBerat = 0;
        $('.produk-item').each(function() {
            let select = $(this).find('.produk-select');
            let jumlah = parseInt($(this).find('.jumlah-input').val()) || 0;
            let harga = parseInt(select.find('option:selected').data('harga')) || 0;
            let berat = parseInt(select.find('option:selected').data('berat')) || 0;
            if (harga > 0 && jumlah > 0) {
                subtotal += harga * jumlah;
                totalBerat += berat * jumlah;
            }
        });
        
        let totalBeratKg = Math.ceil(totalBerat / 1000);
        if(totalBerat > 0 && totalBeratKg == 0) totalBeratKg = 1;
        let ongkir = totalBeratKg * tarif_per_kg;
        
        // Handle diskon
        if (kupon.id) {
            $('#summary-diskon-row').removeClass('hidden');
            $('#summary-diskon').text('- ' + formatRupiah(kupon.diskon));
        } else {
            $('#summary-diskon-row').addClass('hidden');
        }

        let grandTotal = subtotal + ongkir - kupon.diskon;

        $('#summary-subtotal').text(formatRupiah(subtotal));
        $('#summary-berat').text(totalBeratKg);
        $('#summary-ongkir').text(formatRupiah(ongkir));
        $('#summary-grandtotal').text(formatRupiah(grandTotal));
    }
    
    // --- Event Listeners ---
    $('#add-produk').on('click', function() {
        var template = $('#produk-template > .produk-item').clone(true);
        $('#produk-container').append(template);
    }).click(); // Panggil sekali saat load agar 1 baris muncul

    $('#produk-container').on('click', '.remove-produk', function() {
        if ($('.produk-item').length > 1) { $(this).closest('.produk-item').remove(); }
        calculateSummary();
    });

    $('#produk-container').on('change', '.produk-select, .jumlah-input', function() {
        calculateSummary();
    });

    $('#apply-kupon-btn').on('click', function() {
        let kode_kupon = $('#kode_kupon').val();
        let subtotal = 0;
        $('.produk-item').each(function() {
             subtotal += (parseInt($(this).find('option:selected').data('harga')) || 0) * (parseInt($(this).find('.jumlah-input').val()) || 0);
        });

        $.ajax({
            url: 'cek_kupon.php',
            type: 'POST',
            data: { kode_kupon: kode_kupon, subtotal: subtotal },
            dataType: 'json',
            success: function(response) {
                $('#kupon-feedback').text(response.message).removeClass('text-red-500 text-green-500').addClass(response.success ? 'text-green-500' : 'text-red-500');
                if (response.success) {
                    kupon.id = response.kupon_id;
                    kupon.diskon = response.diskon;
                    $('#kuponId').val(kupon.id);
                    $('#diskonAmount').val(kupon.diskon);
                    $('#kode_kupon').prop('disabled', true);
                    $('#apply-kupon-btn').text('Hapus').removeClass('bg-gray-800 hover:bg-gray-900').addClass('bg-red-500 hover:bg-red-600').attr('id', 'remove-kupon-btn');
                }
                calculateSummary();
            }
        });
    });
    
    // Dinamis ganti tombol Terapkan -> Hapus
    $(document).on('click', '#remove-kupon-btn', function() {
        kupon = { id: null, diskon: 0 };
        $('#kuponId').val('');
        $('#diskonAmount').val('');
        $('#kode_kupon').val('').prop('disabled', false);
        $('#kupon-feedback').text('');
        $(this).text('Terapkan').removeClass('bg-red-500 hover:bg-red-600').addClass('bg-gray-800 hover:bg-gray-900').attr('id', 'apply-kupon-btn');
        calculateSummary();
    });

    // --- Helper ---
    function formatRupiah(angka) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka); }
});
</script>

<?php require_once '../../includes/footer.php'; ?>