document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const userId = this.dataset.id;
            console.log('Attempting to ban user with ID:', userId);
            
            if(!confirm("Bu kullanıcıyı banlamak istediğinize emin misiniz?")) {
                return;
            }

            try {
                console.log('Sending request to:', window.location.href);
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({id: userId})
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                // Response text'i al
                const responseText = await response.text();
                console.log('Raw server response:', responseText);

                // JSON parse etmeyi dene
                let data;
                try {
                    data = JSON.parse(responseText);
                    console.log('Parsed response data:', data);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error(`JSON parse error: ${e.message}. Response: ${responseText}`);
                }
                
                if (!data.success) {
                    throw new Error(data.error || "Ban işlemi başarısız");
                }

                // Başarılı ise
                this.closest('tr').remove();
                alert("Kullanıcı başarıyla banlandı!");
            } catch (error) {
                console.error("Hata Detayı:", error);
                alert("Hata: " + error.message);
            }
        });
    });
});