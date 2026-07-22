 
    <style>
 

        .main-card {
            border-radius: 15px;
            overflow: hidden;
        }

        .header {
            background: #0d6efd;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .header .icon {
            font-size: 40px;
        }

        .result-box {
            display: none;
        }

        .result-box.show {
            display: block;
        }

        .time-display {
            font-size: 26px;
            font-weight: bold;
            color: #198754;
        }
    </style>
 

<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-lg main-card">

                <!-- Header -->
                <div class="header">
                    <div class="icon">⏳</div>
                    <h4>সময়ের হিসাব</h4>
                    <p class="mb-0">শুরু ও শেষ তারিখ দিয়ে সময় বের করুন</p>
                </div>

                <!-- Body -->
                <div class="card-body p-4">

                    <div class="mb-3">
                        <label class="form-label">শুরুর তারিখ</label>
                        <input type="date" id="startDate" class="form-control" value="<?php echo date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">শেষ তারিখ</label>
                      <input type="date" id="endDate" class="form-control" value="<?php echo date('Y-m-d') ?>">
                    </div>

                    <button class="btn btn-primary w-100" onclick="calculateTime()">
                        হিসাব করুন
                    </button>

                    <!-- Result -->
                    <div class="result-box mt-4 text-center" id="result">

                        <h5 class="mb-3">📊 মোট সময়</h5>

                        <div class="time-display mb-2" id="timeDisplay"></div>

                        <div class="text-muted" id="extraInfo"></div>

                    </div>

                </div>
 

            </div>

        </div>
    </div>
</div>

<!-- JS -->
<script>
function calculateTime() {
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);

    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        alert("সঠিক তারিখ দিন");
        return;
    }

    if (startDate > endDate) {
        alert("শুরুর তারিখ আগে হতে হবে");
        return;
    }

    const diffTime = Math.abs(endDate - startDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    let years = endDate.getFullYear() - startDate.getFullYear();
    let months = endDate.getMonth() - startDate.getMonth();
    let days = endDate.getDate() - startDate.getDate();

    if (days < 0) {
        months--;
        const prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0);
        days += prevMonth.getDate();
    }

    if (months < 0) {
        years--;
        months += 12;
    }

    const totalMonths = (years * 12) + months;

    document.getElementById('timeDisplay').innerHTML = `
        ${years} বছর ${months} মাস ${days} দিন
        <br>
        <small class="text-secondary">(${totalMonths} মাস ${days} দিন)</small>
    `;

    document.getElementById('extraInfo').innerHTML = `
        মোট দিন: <strong>${diffDays}</strong>
    `;

    document.getElementById('result').classList.add('show');
}

// Auto run
window.onload = calculateTime;
</script>

</body>
</html>