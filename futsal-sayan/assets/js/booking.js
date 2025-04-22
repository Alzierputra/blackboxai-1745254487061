function checkJadwal() {
    const tanggal = document.getElementById('tanggal').value;
    const lapanganId = document.getElementById('lapangan_id').value;
    
    if (tanggal && lapanganId) {
        fetch(`check_jadwal.php?tanggal=${tanggal}&lapangan_id=${lapanganId}`)
            .then(response => response.json())
            .then(data => {
                const jadwalContainer = document.getElementById('jadwal-container');
                jadwalContainer.innerHTML = '';
                
                if (data.jadwal.length > 0) {
                    let html = '<div class="mb-4"><p class="font-semibold mb-2">Jadwal Booking pada ' + formatTanggal(tanggal) + ':</p>';
                    html += '<div class="space-y-2">';
                    
                    data.jadwal.forEach(booking => {
                        const statusClass = booking.status_pembayaran === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
                        html += `
                            <div class="bg-gray-50 p-2 rounded text-sm">
                                <span class="font-medium">${booking.jam_mulai} - ${booking.jam_selesai}</span>
                                <span class="text-gray-600"> • ${booking.nama_user}</span>
                                <span class="${statusClass} text-xs px-2 py-1 rounded-full ml-2">
                                    ${booking.status_pembayaran === 'pending' ? 'Menunggu Pembayaran' : 'Terkonfirmasi'}
                                </span>
                            </div>
                        `;
                    });
                    
                    html += '</div></div>';
                    jadwalContainer.innerHTML = html;
                } else {
                    jadwalContainer.innerHTML = `
                        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                            <i class="fas fa-info-circle mr-2"></i>
                            Belum ada booking untuk tanggal ${formatTanggal(tanggal)}
                        </div>
                    `;
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

function formatTanggal(tanggal) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(tanggal).toLocaleDateString('id-ID', options);
}

// Event listeners
document.getElementById('tanggal').addEventListener('change', checkJadwal);
document.getElementById('lapangan_id').addEventListener('change', checkJadwal);
