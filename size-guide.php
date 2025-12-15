<?php
/**
 * Halaman Size Guide
 * Menampilkan panduan ukuran untuk membantu customer memilih size yang tepat
 * RetroLoved E-Commerce System
 */

session_start();
require_once 'config/database.php';
$base_url = '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Size Guide - RetroLoved</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/toast.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- PAGE HERO -->
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Panduan Ukuran</h1>
            <p class="page-subtitle">Panduan ukuran untuk membantu Anda menemukan fit yang sempurna</p>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content-section">
        <div class="container">
            <div class="content-wrapper">
                
                <!-- How to Measure -->
                <div class="content-block">
                    <h2 class="content-title">Cara Mengukur</h2>
                    <p class="content-text">Ikuti panduan berikut untuk mengukur dengan akurat. Gunakan meteran kain untuk hasil terbaik.</p>
                    
                    <div class="measurement-grid">
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2a5 5 0 0 1 5 5v10a5 5 0 0 1-10 0V7a5 5 0 0 1 5-5z"></path>
                                </svg>
                            </div>
                            <h4>Dada/Bust</h4>
                            <p>Ukur bagian terlebar dari dada, di bawah ketiak, dengan meteran mengelilingi tubuh secara horizontal</p>
                        </div>
                        
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <ellipse cx="12" cy="12" rx="10" ry="6"></ellipse>
                                </svg>
                            </div>
                            <h4>Pinggang</h4>
                            <p>Ukur bagian terkecil dari pinggang, biasanya di sekitar pusar atau sedikit di atasnya</p>
                        </div>
                        
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2v20"></path>
                                    <path d="M8 12a4 4 0 0 1 8 0"></path>
                                </svg>
                            </div>
                            <h4>Pinggul</h4>
                            <p>Ukur bagian terlebar dari pinggul, sekitar 20cm di bawah pinggang</p>
                        </div>
                        
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="2" x2="12" y2="22"></line>
                                    <path d="M5 12H2a10 10 0 0 0 20 0h-3"></path>
                                </svg>
                            </div>
                            <h4>Bahu</h4>
                            <p>Ukur dari ujung bahu kiri ke ujung bahu kanan, melalui bagian belakang leher</p>
                        </div>
                        
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path>
                                </svg>
                            </div>
                            <h4>Panjang Lengan</h4>
                            <p>Ukur dari bahu hingga pergelangan tangan dengan lengan sedikit ditekuk</p>
                        </div>
                        
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <polyline points="5 12 12 19 19 12"></polyline>
                                </svg>
                            </div>
                            <h4>Panjang</h4>
                            <p>Untuk atasan: ukur dari bahu tertinggi hingga ujung bawah. Untuk bawahan: dari pinggang hingga ujung kaki</p>
                        </div>
                    </div>
                </div>

                <!-- Size Charts -->
                <div class="content-block">
                    <h2 class="content-title">Tabel Ukuran</h2>
                    
                    <!-- Women's Tops -->
                    <div class="size-table-container">
                        <h3 class="size-table-title">Atasan Wanita</h3>
                        <div class="table-responsive">
                            <table class="size-table">
                                <thead>
                                    <tr>
                                        <th>Ukuran</th>
                                        <th>Dada (cm)</th>
                                        <th>Pinggang (cm)</th>
                                        <th>Panjang (cm)</th>
                                        <th>Bahu (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>XS</strong></td>
                                        <td>80-84</td>
                                        <td>60-64</td>
                                        <td>58-60</td>
                                        <td>36-38</td>
                                    </tr>
                                    <tr>
                                        <td><strong>S</strong></td>
                                        <td>84-88</td>
                                        <td>64-68</td>
                                        <td>60-62</td>
                                        <td>38-40</td>
                                    </tr>
                                    <tr>
                                        <td><strong>M</strong></td>
                                        <td>88-92</td>
                                        <td>68-72</td>
                                        <td>62-64</td>
                                        <td>40-42</td>
                                    </tr>
                                    <tr>
                                        <td><strong>L</strong></td>
                                        <td>92-96</td>
                                        <td>72-76</td>
                                        <td>64-66</td>
                                        <td>42-44</td>
                                    </tr>
                                    <tr>
                                        <td><strong>XL</strong></td>
                                        <td>96-100</td>
                                        <td>76-80</td>
                                        <td>66-68</td>
                                        <td>44-46</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Women's Bottoms -->
                    <div class="size-table-container">
                        <h3 class="size-table-title">Bawahan Wanita</h3>
                        <div class="table-responsive">
                            <table class="size-table">
                                <thead>
                                    <tr>
                                        <th>Ukuran</th>
                                        <th>Pinggang (cm)</th>
                                        <th>Pinggul (cm)</th>
                                        <th>Panjang Kaki (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>XS / 26</strong></td>
                                        <td>60-64</td>
                                        <td>86-90</td>
                                        <td>76-78</td>
                                    </tr>
                                    <tr>
                                        <td><strong>S / 27-28</strong></td>
                                        <td>64-68</td>
                                        <td>90-94</td>
                                        <td>78-80</td>
                                    </tr>
                                    <tr>
                                        <td><strong>M / 29-30</strong></td>
                                        <td>68-72</td>
                                        <td>94-98</td>
                                        <td>80-82</td>
                                    </tr>
                                    <tr>
                                        <td><strong>L / 31-32</strong></td>
                                        <td>72-76</td>
                                        <td>98-102</td>
                                        <td>82-84</td>
                                    </tr>
                                    <tr>
                                        <td><strong>XL / 33-34</strong></td>
                                        <td>76-82</td>
                                        <td>102-108</td>
                                        <td>84-86</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Men's Tops -->
                    <div class="size-table-container">
                        <h3 class="size-table-title">Atasan Pria</h3>
                        <div class="table-responsive">
                            <table class="size-table">
                                <thead>
                                    <tr>
                                        <th>Ukuran</th>
                                        <th>Dada (cm)</th>
                                        <th>Pinggang (cm)</th>
                                        <th>Panjang (cm)</th>
                                        <th>Bahu (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>S</strong></td>
                                        <td>88-92</td>
                                        <td>74-78</td>
                                        <td>68-70</td>
                                        <td>42-44</td>
                                    </tr>
                                    <tr>
                                        <td><strong>M</strong></td>
                                        <td>92-96</td>
                                        <td>78-82</td>
                                        <td>70-72</td>
                                        <td>44-46</td>
                                    </tr>
                                    <tr>
                                        <td><strong>L</strong></td>
                                        <td>96-100</td>
                                        <td>82-86</td>
                                        <td>72-74</td>
                                        <td>46-48</td>
                                    </tr>
                                    <tr>
                                        <td><strong>XL</strong></td>
                                        <td>100-104</td>
                                        <td>86-90</td>
                                        <td>74-76</td>
                                        <td>48-50</td>
                                    </tr>
                                    <tr>
                                        <td><strong>XXL</strong></td>
                                        <td>104-110</td>
                                        <td>90-96</td>
                                        <td>76-78</td>
                                        <td>50-52</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Men's Bottoms -->
                    <div class="size-table-container">
                        <h3 class="size-table-title">Bawahan Pria</h3>
                        <div class="table-responsive">
                            <table class="size-table">
                                <thead>
                                    <tr>
                                        <th>Ukuran</th>
                                        <th>Pinggang (cm)</th>
                                        <th>Pinggul (cm)</th>
                                        <th>Panjang Kaki (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>28</strong></td>
                                        <td>71-74</td>
                                        <td>88-91</td>
                                        <td>76-78</td>
                                    </tr>
                                    <tr>
                                        <td><strong>30</strong></td>
                                        <td>76-79</td>
                                        <td>93-96</td>
                                        <td>78-80</td>
                                    </tr>
                                    <tr>
                                        <td><strong>32</strong></td>
                                        <td>81-84</td>
                                        <td>98-101</td>
                                        <td>80-82</td>
                                    </tr>
                                    <tr>
                                        <td><strong>34</strong></td>
                                        <td>86-89</td>
                                        <td>103-106</td>
                                        <td>82-84</td>
                                    </tr>
                                    <tr>
                                        <td><strong>36</strong></td>
                                        <td>91-94</td>
                                        <td>108-111</td>
                                        <td>84-86</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="content-block">
                    <h2 class="content-title">Tips Memilih Ukuran</h2>
                    
                    <div class="tips-grid">
                        <div class="tip-card">
                            <div class="tip-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </div>
                            <h4>Cek Detail Produk</h4>
                            <p>Setiap produk memiliki ukuran spesifik yang tercantum di deskripsi. Pastikan untuk membaca dengan teliti.</p>
                        </div>
                        
                        <div class="tip-card">
                            <div class="tip-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </div>
                            <h4>Ukuran Vintage</h4>
                            <p>Ukuran vintage berbeda dengan sizing modern. Selalu ukur tubuh Anda dan bandingkan dengan ukuran produk.</p>
                        </div>
                        
                        <div class="tip-card">
                            <div class="tip-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </div>
                            <h4>Beri Ruang Gerak</h4>
                            <p>Sisakan ruang 2-3cm dari ukuran aktual untuk kenyamanan bergerak.</p>
                        </div>
                        
                        <div class="tip-card">
                            <div class="tip-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </div>
                            <h4>Tanya Kami</h4>
                            <p>Jika ragu, jangan ragu untuk menghubungi customer service kami untuk bantuan pemilihan size.</p>
                        </div>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="content-block alert-block">
                    <h2 class="content-title">Catatan Penting</h2>
                    <div class="alert-list">
                        <div class="alert-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>Ukuran dapat bervariasi 1-2cm karena pengukuran manual</p>
                        </div>
                        <div class="alert-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>Produk vintage mungkin memiliki fit yang berbeda dengan standar modern</p>
                        </div>
                        <div class="alert-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>Beberapa produk mungkin telah mengalami penyusutan akibat usia dan pencucian</p>
                        </div>
                        <div class="alert-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>Selalu cek detail ukuran spesifik di halaman produk sebelum membeli</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <div class="container" style="padding-top: 40px; padding-bottom: 80px;">
        <div class="faq-contact">
            <h3>Butuh Bantuan Memilih Ukuran?</h3>
            <p>Tim customer service kami siap membantu Anda menemukan ukuran yang tepat</p>
            <a href="javascript:void(0)" onclick="showContactSupportModal()" class="btn btn-primary">Hubungi Support</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
