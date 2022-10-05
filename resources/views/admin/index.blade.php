@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-xl-3">
        <div class="card mb-4">
            <div class="card-header">
                Accounts
            </div>
            <div class="card-body">
                <canvas id="myChart" width="400" height="400"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3">
        <div class="card mb-4">
            <div class="card-header">
                Sessions
            </div>
            <div class="card-body">
                <canvas id="myChart2" width="400" height="400"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-3">
    <div class="card mb-4">
            <div class="card-header">
                Payment Info
            </div>
            <div class="card-body">
                <b>Pending payment to tutor(s):</b><br/>
                User's Revenue: P<?php echo $user_revenue-$business_revenue; ?><br/>
                Business's Revenue: P<?php echo $business_revenue; ?><br/>
                <hr/>
                <b>Completed payment to tutor(s):</b><br/>
                User's Revenue: P<?php echo $user_revenue2-$business_revenue2; ?><br/>
                Business's Revenue: P<?php echo $business_revenue2; ?>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                Statistics
            </div>
            <div class="card-body">
                <b>Online tutor(s):</b> <?php echo $online; ?><br/>
                <b>Overall user(s):</b> <?php echo $users; ?><br/>
                <b>Overall freelancer(s):</b> <?php echo $freelancers; ?>
            </div>
        </div>
    </div>
</div>
<script>
const ctx = document.getElementById('myChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['User', 'Freelancer (Unverified)', 'Freelancer (Verified)'],
        datasets: [{
            label: '# of Votes',
            data: [<?php echo $user; ?>, <?php echo $freelancer; ?>, <?php echo $freelancer2; ?>],
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
    }
    /*
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }*/
});

var labels = ['<?php echo $sessions[0][0]; ?>', '<?php echo $sessions[1][0]; ?>', '<?php echo $sessions[2][0]; ?>', '<?php echo $sessions[3][0]; ?>', '<?php echo $sessions[4][0]; ?>', '<?php echo $sessions[5][0]; ?>'];
var datas = [<?php echo $sessions[0][1]; ?>, <?php echo $sessions[1][1]; ?>, <?php echo $sessions[2][1]; ?>, <?php echo $sessions[3][1]; ?>, <?php echo $sessions[4][1]; ?>, <?php echo $sessions[5][1]; ?>];
labels.reverse();
datas.reverse();

const ctx2 = document.getElementById('myChart2').getContext('2d');
const myChart2 = new Chart(ctx2, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Session',
            data: datas,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins:{
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                stacked: true
            }
        }
    }
});
</script>
@endsection