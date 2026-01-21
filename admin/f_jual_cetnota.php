<?php
  ob_start();
  $cDtc  = $_POST['dtc'];
  $nKali = $_POST['kopi'];
?>
  <script>
  console.log('🔵 Cetak nota script loaded, dtc: <?=$cDtc?>');
  (function() {
    // Flag untuk mencegah double execution
    var printExecuted = false;
    var printKey = 'print_' + '<?=$cDtc?>';
    
    console.log('🔵 Checking print key:', printKey);
    
    // Cek apakah sudah ada flag di sessionStorage
    if (sessionStorage.getItem(printKey)) {
      console.log('⚠️ Print already executed, skipping...');
      return;
    }
    
    console.log('✅ Starting print process...');
    
    // Set flag
    sessionStorage.setItem(printKey, '1');
    
    // Hapus flag setelah 3 detik
    setTimeout(function() {
      sessionStorage.removeItem(printKey);
    }, 3000);
    
    async function fetchJSON(url, options = {}) {
          try {
          const res = await fetch(url, options);

          // coba parse JSON
          try {
              return await res.json();
          } catch (jsonErr) {
              // kalau gagal parse → ambil raw text
              const raw = await res.text();
              console.error("❌ JSON Parse Error:", jsonErr.message);
              console.log("📜 RAW RESPONSE:\n", raw);
              throw jsonErr; // tetap lempar error biar ketahuan
          }

          } catch (err) {
          console.error("❌ Fetch Error:", err);
          throw err;
          }
    }

    console.log('📡 Fetching nota data from get_nota.php?dts=<?=$cDtc?>');
    fetch("get_nota.php?dts=<?=$cDtc?>")
    .then(res => {
      console.log('📥 Response status from get_nota.php:', res.status);
      if (!res.ok) {
        throw new Error('Failed to fetch nota data: ' + res.status);
      }
      return res.json();
    })
    .then(data => {
          console.log("✅ Parsed JSON from get_nota.php:", data);
          if (!data.success || !data.data) {
            console.error('❌ Invalid data structure:', data);
            return;
          }
          console.log('📤 Sending print request to http://localhost:3000/print/nota');
          fetch("http://localhost:3000/print/nota", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(data.data)
          })
          .then(res => {
            const ct = res.headers.get('content-type') || '';
            console.log('📥 Print response status:', res.status, 'Content-Type:', ct);
          if (!res.ok) {
              // non-2xx -> parse body jika JSON, atau ambil text untuk debugging
              if (ct.includes('application/json')) {
              return res.json().then(obj => {
                console.error('❌ Print server error:', obj);
                return Promise.reject({ status: res.status, body: obj });
              });
              } else {
              return res.text().then(txt => {
                console.error('❌ Print server error (text):', txt);
                return Promise.reject({ status: res.status, body: txt });
              });
              }
          }
          // 2xx -> kembalikan JSON bila ada, atau text
          if (ct.includes('application/json')) return res.json();
            return res.text();
          })
          .then(result => {
            console.log('✅ Print request successful! Response:', result);
          })
          .catch(err => {
            console.error('❌ Print request failed:', err);
            if (err.message && err.message.includes('Failed to fetch')) {
              console.error('⚠️ Print server mungkin tidak berjalan di http://localhost:3000');
            }
          });

    })
    .catch(err => {
      console.error('❌ Failed to fetch nota data:', err);
    });
  })();
  </script>
<?php
  $html = ob_get_contents(); 
  ob_end_clean();
  echo json_encode(array('hasil'=>$html));
?>