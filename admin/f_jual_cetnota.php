<?php
  ob_start();
  $cDtc  = $_POST['dtc'];
  $nKali = $_POST['kopi'];
?>
  <script>
  (function() {
    // Flag untuk mencegah double execution
    var printExecuted = false;
    var printKey = 'print_' + '<?=$cDtc?>';
    
    // Cek apakah sudah ada flag di sessionStorage
    if (sessionStorage.getItem(printKey)) {
      console.log('Print already executed, skipping...');
      return;
    }
    
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

    fetch("get_nota.php?dts=<?=$cDtc?>")
    .then(res => res.json())
    .then(data => {
          console.log("✅ Parsed JSON:", data);
          fetch("http://localhost:3000/print/nota", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(data.data)
          })
          .then(res => {
            const ct = res.headers.get('content-type') || '';
          if (!res.ok) {
              // non-2xx -> parse body jika JSON, atau ambil text untuk debugging
              if (ct.includes('application/json')) {
              return res.json().then(obj => Promise.reject({ status: res.status, body: obj }));
              } else {
              return res.text().then(txt => Promise.reject({ status: res.status, body: txt }));
              }
          }
          // 2xx -> kembalikan JSON bila ada, atau text
          if (ct.includes('application/json')) return res.json();
            return res.text();
          })
          .then(result => {
            console.log('Response from print bridge:', result);
          })
          .catch(err => {
            console.error('Print request failed:', err);
          });

    })
    .catch(err => {
      console.error('Failed to fetch nota data:', err);
    });
  })();
  </script>
<?php
  $html = ob_get_contents(); 
  ob_end_clean();
  echo json_encode(array('hasil'=>$html));
?>