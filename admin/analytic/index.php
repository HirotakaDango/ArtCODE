<?php
// admin/analytic/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <title>Admin Analytics Dashboard</title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      .chart-container {
        position: relative;
        height: 500px;
        width: 100%;
      }
      .stat-card {
        transition: all 0.3s;
      }
      .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      }
    </style>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../navbar.php'); ?>
          <div class="container py-4">
            <h1 class="mb-4">Analytics Dashboard</h1>

            <!-- New Section: Content and User Activity -->
            <div class="card mb-4 rounded-4 border-0 bg-dark-subtle" id="contentSection">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <h5 class="card-title mb-0">Content and User Activity</h5>
                  <select id="timeRangeSelect" class="form-select rounded border-0 bg-secondary-subtle" style="width: auto;">
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="this_year">This Year</option>
                    <option value="all_time">All Time</option>
                  </select>
                </div>
                
                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4 fw-medium">
                  <div class="col">
                    <div class="card stat-card h-100 border-0 bg-secondary-subtle">
                      <div class="card-body text-center">
                        <i class="bi bi-image fs-1 text-primary mb-2"></i>
                        <h6 class="card-title">Images</h6>
                        <p class="card-text fs-4 fw-bold" id="imagesCount">Loading...</p>
                      </div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="card stat-card h-100 border-0 bg-secondary-subtle">
                      <div class="card-body text-center">
                        <i class="bi bi-camera-video fs-1 text-danger mb-2"></i>
                        <h6 class="card-title">Videos</h6>
                        <p class="card-text fs-4 fw-bold" id="videosCount">Loading...</p>
                      </div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="card stat-card h-100 border-0 bg-secondary-subtle">
                      <div class="card-body text-center">
                        <i class="bi bi-music-note-beamed fs-1 text-success mb-2"></i>
                        <h6 class="card-title">Music</h6>
                        <p class="card-text fs-4 fw-bold" id="musicCount">Loading...</p>
                      </div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="card stat-card h-100 border-0 bg-secondary-subtle">
                      <div class="card-body text-center">
                        <i class="bi bi-eye fs-1 text-info mb-2"></i>
                        <h6 class="card-title">Visits</h6>
                        <p class="card-text fs-4 fw-bold" id="visitsCount">Loading...</p>
                      </div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="card stat-card h-100 border-0 bg-secondary-subtle">
                      <div class="card-body text-center">
                        <i class="bi bi-people fs-1 text-warning mb-2"></i>
                        <h6 class="card-title">Users</h6>
                        <p class="card-text fs-4 fw-bold" id="usersCount">Loading...</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card mb-4 rounded-4 border-0 bg-dark-subtle" id="regionSection">
              <div class="card-body">
                <h5 class="card-title mb-4">User Regions</h5>
                <ul class="list-group" id="regionsList">
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    Loading regions data...
                  </li>
                </ul>
              </div>
            </div>

            <!-- Stats Overview -->
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 mb-4" id="mediaSection">
              <div class="col">
                <div class="card stat-card rounded-4 bg-primary text-white">
                  <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text fs-2" id="totalUsers">Loading...</p>
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="card stat-card rounded-4 bg-success text-white">
                  <div class="card-body">
                    <h5 class="card-title">Total Visits</h5>
                    <p class="card-text fs-2" id="totalVisits">Loading...</p>
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="card stat-card rounded-4 bg-info text-white">
                  <div class="card-body">
                    <h5 class="card-title">Total Media</h5>
                    <p class="card-text fs-2" id="totalMedia">Loading...</p>
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="card stat-card rounded-4 bg-warning text-dark">
                  <div class="card-body">
                    <h5 class="card-title">Total Storage</h5>
                    <p class="card-text fs-2" id="totalSize">Loading...</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Charts -->
            <div class="row g-4">
              <div class="col-12" id="activitySection">
                <div class="card rounded-4 border-0 bg-dark-subtle">
                  <div class="card-body">
                    <h5 class="card-title">Activity Overview</h5>
                    <div class="chart-container">
                      <canvas id="activityChart"></canvas>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6" id="analysisSection">
                <div class="card rounded-4 border-0 bg-dark-subtle">
                  <div class="card-body">
                    <h5 class="card-title">Content Distribution</h5>
                    <div class="chart-container">
                      <canvas id="contentDistributionChart"></canvas>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card rounded-4 border-0 bg-dark-subtle">
                  <div class="card-body">
                    <h5 class="card-title">User Growth</h5>
                    <div class="chart-container">
                      <canvas id="userGrowthChart"></canvas>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
    $(document).ready(function() {
      let activityChart, contentDistributionChart, userGrowthChart;

      function updateData() {
        $.ajax({
        url: 'data.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          // Update stats
          $('#totalUsers').text(data.user_count.toLocaleString());
          $('#totalVisits').text(data.visit_count_total.toLocaleString());
          $('#totalMedia').text((data.image_count + data.image_child_count + data.videos_count + data.music_count).toLocaleString());
          $('#totalSize').text(data.total_size_data + ' MB');

          // Update new section
          updateContentUserActivity(data);

          // Update regions
          updateRegions(data);

          // Update charts
          updateActivityChart(data);
          updateContentDistributionChart(data);
          updateUserGrowthChart(data);
        },
        error: function(xhr, status, error) {
          console.error('Error fetching data:', error);
        }
        });
      }

      function updateContentUserActivity(data) {
        const timeRange = $('#timeRangeSelect').val();
        $('#imagesCount').text(data.images[timeRange].toLocaleString());
        $('#videosCount').text(data.videos[timeRange].toLocaleString());
        $('#musicCount').text(data.music[timeRange].toLocaleString());
        $('#visitsCount').text(data.visits[timeRange].toLocaleString());
        $('#usersCount').text(data.users[timeRange].toLocaleString());
      }

      $('#timeRangeSelect').change(function() {
        updateData();
      });

      function updateRegions(data) {
        const regionsList = $('#regionsList');
        regionsList.empty();

        Object.entries(data.regions).forEach(([region, count]) => {
        regionsList.append(`
          <li class="list-group-item d-flex justify-content-between align-items-center">
          ${region}
          <span class="badge bg-primary rounded-pill">${count.toLocaleString()}</span>
          </li>
        `);
        });
      }

      // Initial data load
      updateData();


      // Add this line to your existing updateData function
      updateRegions(data);

      function updateActivityChart(data) {
        const ctx = document.getElementById('activityChart').getContext('2d');
        const chartData = {
          labels: Object.keys(data.uploads_by_date.images).reverse(),
          datasets: [
            {
              label: 'Images Uploaded',
              data: Object.values(data.uploads_by_date.images).reverse(),
              borderColor: 'rgba(255, 99, 132, 1)',
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
            },
            {
              label: 'Videos Uploaded',
              data: Object.values(data.uploads_by_date.videos).reverse(),
              borderColor: 'rgba(54, 162, 235, 1)',
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
            },
            {
              label: 'Music Uploaded',
              data: Object.values(data.uploads_by_date.music).reverse(),
              borderColor: 'rgba(75, 192, 192, 1)',
              backgroundColor: 'rgba(75, 192, 192, 0.2)',
            }
          ]
        };

        if (activityChart) {
          activityChart.data = chartData;
          activityChart.update();
        } else {
          activityChart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
              responsive: true,
              maintainAspectRatio: false,
              scales: {
                y: {
                  beginAtZero: true
                }
              }
            }
          });
        }
      }

      function updateContentDistributionChart(data) {
        const ctx = document.getElementById('contentDistributionChart').getContext('2d');
        const chartData = {
          labels: ['Images', 'Videos', 'Music', 'Novels'],
          datasets: [{
            data: [
              data.image_count + data.image_child_count,
              data.videos_count,
              data.music_count,
              data.novel_count_total
            ],
            backgroundColor: [
              'rgba(255, 99, 132, 0.8)',
              'rgba(54, 162, 235, 0.8)',
              'rgba(75, 192, 192, 0.8)',
              'rgba(255, 206, 86, 0.8)'
            ]
          }]
        };

        if (contentDistributionChart) {
          contentDistributionChart.data = chartData;
          contentDistributionChart.update();
        } else {
          contentDistributionChart = new Chart(ctx, {
            type: 'pie',
            data: chartData,
            options: {
              responsive: true,
              maintainAspectRatio: false
            }
          });
        }
      }

      function updateUserGrowthChart(data) {
        const ctx = document.getElementById('userGrowthChart').getContext('2d');
        const chartData = {
          labels: data.join_dates,
          datasets: [{
            label: 'New Users',
            data: data.join_counts,
            borderColor: 'rgba(153, 102, 255, 1)',
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
          }]
        };

        if (userGrowthChart) {
          userGrowthChart.data = chartData;
          userGrowthChart.update();
        } else {
          userGrowthChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
              responsive: true,
              maintainAspectRatio: false,
              scales: {
                y: {
                  beginAtZero: true
                }
              }
            }
          });
        }
      }

      // Initial data load
      updateData();

      // Refresh data every 30 seconds
      setInterval(updateData, 30000);
    });
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>