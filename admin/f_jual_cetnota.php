<?php
  ob_start();
  $cDtc  = $_POST['dtc'];
  $nKali = $_POST['kopi'];
?>
  <script>
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
  </script>
<?php
  $html = ob_get_contents(); 
  ob_end_clean();
  echo json_encode(array('hasil'=>$html));
?>