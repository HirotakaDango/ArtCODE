<?php
require_once('../auth.php');

// Replace 'your-database-name.sqlite' with the actual path to your SQLite database file
$database = new SQLite3('../database.sqlite');

// Get the current region of the user from the database
$query = "SELECT region FROM users WHERE email = :email";
$statement = $database->prepare($query);
$statement->bindValue(':email', $_SESSION['email']);
$result = $statement->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$currentregion = $row['region'];

// Process the form submission if the user has selected a new region
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the selected region from the form
  $selectedregion = $_POST['region'];

  // Update the user's region in the database
  $query = "UPDATE users SET region = :region WHERE email = :email";
  $statement = $database->prepare($query);
  $statement->bindValue(':region', $selectedregion);
  $statement->bindValue(':email', $_SESSION['email']);
  $statement->execute();

  // Redirect the user to a success page or any other desired location
  header("Location: region.php");
  exit;
}

// Close the database connection
$database->close();
?>

    <main id="swup" class="transition-main">
      <?php include('setheader.php'); ?>
        <div class="container mb-5 mt-4">
          <div class="d-md-none mb-4">
            <div class="d-flex">
              <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="/settings/">
                <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i>
              </a>
            </div>
          </div>
          <h3 class="fw-bold mb-3">
            Change Region
          </h3>
          <p class="fw-semibold mb-4">Current region: <?php echo $currentregion; ?></p>
          <div class="card border-0 bg-body-tertiary rounded-4 shadow-sm p-4 mb-4">
            <h5 class="fw-bold">
              <i class="bi bi-globe-asia-australia"></i> Change Region
            </h5>
            <p class="text-muted mb-4">Update your region settings to personalize your experience on the website.</p>
            <form method="POST" action="">
              <div class="form-group">
                <label class="fw-semibold mb-3" for="region">Select region:</label>
                <select class="form-select" id="region" name="region">
                  <option value="Afghanistan" <?php if ($currentregion == 'Afghanistan') echo 'selected'; ?>>Afghanistan</option>
                  <option value="Albania" <?php if ($currentregion == 'Albania') echo 'selected'; ?>>Albania</option>
                  <option value="Algeria" <?php if ($currentregion == 'Algeria') echo 'selected'; ?>>Algeria</option>
                  <option value="Andorra" <?php if ($currentregion == 'Andorra') echo 'selected'; ?>>Andorra</option>
                  <option value="Angola" <?php if ($currentregion == 'Angola') echo 'selected'; ?>>Angola</option>
                  <option value="Antigua and Barbuda" <?php if ($currentregion == 'Antigua and Barbuda') echo 'selected'; ?>>Antigua and Barbuda</option>
                  <option value="Argentina" <?php if ($currentregion == 'Argentina') echo 'selected'; ?>>Argentina</option>
                  <option value="Armenia" <?php if ($currentregion == 'Armenia') echo 'selected'; ?>>Armenia</option>
                  <option value="Australia" <?php if ($currentregion == 'Australia') echo 'selected'; ?>>Australia</option>
                  <option value="Austria" <?php if ($currentregion == 'Austria') echo 'selected'; ?>>Austria</option>
                  <option value="Azerbaijan" <?php if ($currentregion == 'Azerbaijan') echo 'selected'; ?>>Azerbaijan</option>
                  <option value="Bahamas" <?php if ($currentregion == 'Bahamas') echo 'selected'; ?>>Bahamas</option>
                  <option value="Bahrain" <?php if ($currentregion == 'Bahrain') echo 'selected'; ?>>Bahrain</option>
                  <option value="Bangladesh" <?php if ($currentregion == 'Bangladesh') echo 'selected'; ?>>Bangladesh</option>
                  <option value="Barbados" <?php if ($currentregion == 'Barbados') echo 'selected'; ?>>Barbados</option>
                  <option value="Belarus" <?php if ($currentregion == 'Belarus') echo 'selected'; ?>>Belarus</option>
                  <option value="Belgium" <?php if ($currentregion == 'Belgium') echo 'selected'; ?>>Belgium</option>
                  <option value="Belize" <?php if ($currentregion == 'Belize') echo 'selected'; ?>>Belize</option>
                  <option value="Benin" <?php if ($currentregion == 'Benin') echo 'selected'; ?>>Benin</option>
                  <option value="Bhutan" <?php if ($currentregion == 'Bhutan') echo 'selected'; ?>>Bhutan</option>
                  <option value="Bolivia" <?php if ($currentregion == 'Bolivia') echo 'selected'; ?>>Bolivia</option>
                  <option value="Bosnia and Herzegovina" <?php if ($currentregion == 'Bosnia and Herzegovina') echo 'selected'; ?>>Bosnia and Herzegovina</option>
                  <option value="Botswana" <?php if ($currentregion == 'Botswana') echo 'selected'; ?>>Botswana</option>
                  <option value="Brazil" <?php if ($currentregion == 'Brazil') echo 'selected'; ?>>Brazil</option>
                  <option value="Brunei" <?php if ($currentregion == 'Brunei') echo 'selected'; ?>>Brunei</option>
                  <option value="Bulgaria" <?php if ($currentregion == 'Bulgaria') echo 'selected'; ?>>Bulgaria</option>
                  <option value="Burkina Faso" <?php if ($currentregion == 'Burkina Faso') echo 'selected'; ?>>Burkina Faso</option>
                  <option value="Burundi" <?php if ($currentregion == 'Burundi') echo 'selected'; ?>>Burundi</option>
                  <option value="Cambodia" <?php if ($currentregion == 'Cambodia') echo 'selected'; ?>>Cambodia</option>
                  <option value="Cameroon" <?php if ($currentregion == 'Cameroon') echo 'selected'; ?>>Cameroon</option>
                  <option value="Canada" <?php if ($currentregion == 'Canada') echo 'selected'; ?>>Canada</option>
                  <option value="Cape Verde" <?php if ($currentregion == 'Cape Verde') echo 'selected'; ?>>Cape Verde</option>
                  <option value="Central African Republic" <?php if ($currentregion == 'Central African Republic') echo 'selected'; ?>>Central African Republic</option>
                  <option value="Chad" <?php if ($currentregion == 'Chad') echo 'selected'; ?>>Chad</option>
                  <option value="Chile" <?php if ($currentregion == 'Chile') echo 'selected'; ?>>Chile</option>
                  <option value="China" <?php if ($currentregion == 'China') echo 'selected'; ?>>China</option>
                  <option value="Colombia" <?php if ($currentregion == 'Colombia') echo 'selected'; ?>>Colombia</option>
                  <option value="Comoros" <?php if ($currentregion == 'Comoros') echo 'selected'; ?>>Comoros</option>
                  <option value="Congo (Brazzaville)" <?php if ($currentregion == 'Congo (Brazzaville)') echo 'selected'; ?>>Congo (Brazzaville)</option>
                  <option value="Congo (Kinshasa)" <?php if ($currentregion == 'Congo (Kinshasa)') echo 'selected'; ?>>Congo (Kinshasa)</option>
                  <option value="Costa Rica" <?php if ($currentregion == 'Costa Rica') echo 'selected'; ?>>Costa Rica</option>
                  <option value="Croatia" <?php if ($currentregion == 'Croatia') echo 'selected'; ?>>Croatia</option>
                  <option value="Cuba" <?php if ($currentregion == 'Cuba') echo 'selected'; ?>>Cuba</option>
                  <option value="Cyprus" <?php if ($currentregion == 'Cyprus') echo 'selected'; ?>>Cyprus</option>
                  <option value="Czech Republic" <?php if ($currentregion == 'Czech Republic') echo 'selected'; ?>>Czech Republic</option>
                  <option value="Denmark" <?php if ($currentregion == 'Denmark') echo 'selected'; ?>>Denmark</option>
                  <option value="Djibouti" <?php if ($currentregion == 'Djibouti') echo 'selected'; ?>>Djibouti</option>
                  <option value="Dominica" <?php if ($currentregion == 'Dominica') echo 'selected'; ?>>Dominica</option>
                  <option value="Dominican Republic" <?php if ($currentregion == 'Dominican Republic') echo 'selected'; ?>>Dominican Republic</option>
                  <option value="East Timor" <?php if ($currentregion == 'East Timor') echo 'selected'; ?>>East Timor</option>
                  <option value="Ecuador" <?php if ($currentregion == 'Ecuador') echo 'selected'; ?>>Ecuador</option>
                  <option value="Egypt" <?php if ($currentregion == 'Egypt') echo 'selected'; ?>>Egypt</option>
                  <option value="El Salvador" <?php if ($currentregion == 'El Salvador') echo 'selected'; ?>>El Salvador</option>
                  <option value="Equatorial Guinea" <?php if ($currentregion == 'Equatorial Guinea') echo 'selected'; ?>>Equatorial Guinea</option>
                  <option value="Eritrea" <?php if ($currentregion == 'Eritrea') echo 'selected'; ?>>Eritrea</option>
                  <option value="Estonia" <?php if ($currentregion == 'Estonia') echo 'selected'; ?>>Estonia</option>
                  <option value="Eswatini" <?php if ($currentregion == 'Eswatini') echo 'selected'; ?>>Eswatini</option>
                  <option value="Ethiopia" <?php if ($currentregion == 'Ethiopia') echo 'selected'; ?>>Ethiopia</option>
                  <option value="Fiji" <?php if ($currentregion == 'Fiji') echo 'selected'; ?>>Fiji</option>
                  <option value="Finland" <?php if ($currentregion == 'Finland') echo 'selected'; ?>>Finland</option>
                  <option value="France" <?php if ($currentregion == 'France') echo 'selected'; ?>>France</option>
                  <option value="Gabon" <?php if ($currentregion == 'Gabon') echo 'selected'; ?>>Gabon</option>
                  <option value="Gambia" <?php if ($currentregion == 'Gambia') echo 'selected'; ?>>Gambia</option>
                  <option value="Georgia" <?php if ($currentregion == 'Georgia') echo 'selected'; ?>>Georgia</option>
                  <option value="Germany" <?php if ($currentregion == 'Germany') echo 'selected'; ?>>Germany</option>
                  <option value="Ghana" <?php if ($currentregion == 'Ghana') echo 'selected'; ?>>Ghana</option>
                  <option value="Greece" <?php if ($currentregion == 'Greece') echo 'selected'; ?>>Greece</option>
                  <option value="Grenada" <?php if ($currentregion == 'Grenada') echo 'selected'; ?>>Grenada</option>
                  <option value="Guatemala" <?php if ($currentregion == 'Guatemala') echo 'selected'; ?>>Guatemala</option>
                  <option value="Guinea" <?php if ($currentregion == 'Guinea') echo 'selected'; ?>>Guinea</option>
                  <option value="Guinea-Bissau" <?php if ($currentregion == 'Guinea-Bissau') echo 'selected'; ?>>Guinea-Bissau</option>
                  <option value="Guyana" <?php if ($currentregion == 'Guyana') echo 'selected'; ?>>Guyana</option>
                  <option value="Haiti" <?php if ($currentregion == 'Haiti') echo 'selected'; ?>>Haiti</option>
                  <option value="Honduras" <?php if ($currentregion == 'Honduras') echo 'selected'; ?>>Honduras</option>
                  <option value="Hungary" <?php if ($currentregion == 'Hungary') echo 'selected'; ?>>Hungary</option>
                  <option value="Iceland" <?php if ($currentregion == 'Iceland') echo 'selected'; ?>>Iceland</option>
                  <option value="India" <?php if ($currentregion == 'India') echo 'selected'; ?>>India</option>
                  <option value="Indonesia" <?php if ($currentregion == 'Indonesia') echo 'selected'; ?>>Indonesia</option>
                  <option value="Iran" <?php if ($currentregion == 'Iran') echo 'selected'; ?>>Iran</option>
                  <option value="Iraq" <?php if ($currentregion == 'Iraq') echo 'selected'; ?>>Iraq</option>
                  <option value="Ireland" <?php if ($currentregion == 'Ireland') echo 'selected'; ?>>Ireland</option>
                  <option value="Israel" <?php if ($currentregion == 'Israel') echo 'selected'; ?>>Israel</option>
                  <option value="Italy" <?php if ($currentregion == 'Italy') echo 'selected'; ?>>Italy</option>
                  <option value="Jamaica" <?php if ($currentregion == 'Jamaica') echo 'selected'; ?>>Jamaica</option>
                  <option value="Japan" <?php if ($currentregion == 'Japan') echo 'selected'; ?>>Japan</option>
                  <option value="Jordan" <?php if ($currentregion == 'Jordan') echo 'selected'; ?>>Jordan</option>
                  <option value="Kazakhstan" <?php if ($currentregion == 'Kazakhstan') echo 'selected'; ?>>Kazakhstan</option>
                  <option value="Kenya" <?php if ($currentregion == 'Kenya') echo 'selected'; ?>>Kenya</option>
                  <option value="Kiribati" <?php if ($currentregion == 'Kiribati') echo 'selected'; ?>>Kiribati</option>
                  <option value="Kuwait" <?php if ($currentregion == 'Kuwait') echo 'selected'; ?>>Kuwait</option>
                  <option value="Kyrgyzstan" <?php if ($currentregion == 'Kyrgyzstan') echo 'selected'; ?>>Kyrgyzstan</option>
                  <option value="Laos" <?php if ($currentregion == 'Laos') echo 'selected'; ?>>Laos</option>
                  <option value="Latvia" <?php if ($currentregion == 'Latvia') echo 'selected'; ?>>Latvia</option>
                  <option value="Lebanon" <?php if ($currentregion == 'Lebanon') echo 'selected'; ?>>Lebanon</option>
                  <option value="Lesotho" <?php if ($currentregion == 'Lesotho') echo 'selected'; ?>>Lesotho</option>
                  <option value="Liberia" <?php if ($currentregion == 'Liberia') echo 'selected'; ?>>Liberia</option>
                  <option value="Libya" <?php if ($currentregion == 'Libya') echo 'selected'; ?>>Libya</option>
                  <option value="Liechtenstein" <?php if ($currentregion == 'Liechtenstein') echo 'selected'; ?>>Liechtenstein</option>
                  <option value="Lithuania" <?php if ($currentregion == 'Lithuania') echo 'selected'; ?>>Lithuania</option>
                  <option value="Luxembourg" <?php if ($currentregion == 'Luxembourg') echo 'selected'; ?>>Luxembourg</option>
                  <option value="Madagascar" <?php if ($currentregion == 'Madagascar') echo 'selected'; ?>>Madagascar</option>
                  <option value="Malawi" <?php if ($currentregion == 'Malawi') echo 'selected'; ?>>Malawi</option>
                  <option value="Malaysia" <?php if ($currentregion == 'Malaysia') echo 'selected'; ?>>Malaysia</option>
                  <option value="Maldives" <?php if ($currentregion == 'Maldives') echo 'selected'; ?>>Maldives</option>
                  <option value="Mali" <?php if ($currentregion == 'Mali') echo 'selected'; ?>>Mali</option>
                  <option value="Malta" <?php if ($currentregion == 'Malta') echo 'selected'; ?>>Malta</option>
                  <option value="Marshall Islands" <?php if ($currentregion == 'Marshall Islands') echo 'selected'; ?>>Marshall Islands</option>
                  <option value="Mauritania" <?php if ($currentregion == 'Mauritania') echo 'selected'; ?>>Mauritania</option>
                  <option value="Mauritius" <?php if ($currentregion == 'Mauritius') echo 'selected'; ?>>Mauritius</option>
                  <option value="Mexico" <?php if ($currentregion == 'Mexico') echo 'selected'; ?>>Mexico</option>
                  <option value="Micronesia" <?php if ($currentregion == 'Micronesia') echo 'selected'; ?>>Micronesia</option>
                  <option value="Moldova" <?php if ($currentregion == 'Moldova') echo 'selected'; ?>>Moldova</option>
                  <option value="Monaco" <?php if ($currentregion == 'Monaco') echo 'selected'; ?>>Monaco</option>
                  <option value="Mongolia" <?php if ($currentregion == 'Mongolia') echo 'selected'; ?>>Mongolia</option>
                  <option value="Montenegro" <?php if ($currentregion == 'Montenegro') echo 'selected'; ?>>Montenegro</option>
                  <option value="Morocco" <?php if ($currentregion == 'Morocco') echo 'selected'; ?>>Morocco</option>
                  <option value="Mozambique" <?php if ($currentregion == 'Mozambique') echo 'selected'; ?>>Mozambique</option>
                  <option value="Myanmar" <?php if ($currentregion == 'Myanmar') echo 'selected'; ?>>Myanmar</option>
                  <option value="Namibia" <?php if ($currentregion == 'Namibia') echo 'selected'; ?>>Namibia</option>
                  <option value="Nauru" <?php if ($currentregion == 'Nauru') echo 'selected'; ?>>Nauru</option>
                  <option value="Nepal" <?php if ($currentregion == 'Nepal') echo 'selected'; ?>>Nepal</option>
                  <option value="Netherlands" <?php if ($currentregion == 'Netherlands') echo 'selected'; ?>>Netherlands</option>
                  <option value="New Zealand" <?php if ($currentregion == 'New Zealand') echo 'selected'; ?>>New Zealand</option>
                  <option value="Nicaragua" <?php if ($currentregion == 'Nicaragua') echo 'selected'; ?>>Nicaragua</option>
                  <option value="Niger" <?php if ($currentregion == 'Niger') echo 'selected'; ?>>Niger</option>
                  <option value="Nigeria" <?php if ($currentregion == 'Nigeria') echo 'selected'; ?>>Nigeria</option>
                  <option value="North Korea" <?php if ($currentregion == 'North Korea') echo 'selected'; ?>>North Korea</option>
                  <option value="North Macedonia" <?php if ($currentregion == 'North Macedonia') echo 'selected'; ?>>North Macedonia</option>
                  <option value="Norway" <?php if ($currentregion == 'Norway') echo 'selected'; ?>>Norway</option>
                  <option value="Oman" <?php if ($currentregion == 'Oman') echo 'selected'; ?>>Oman</option>
                  <option value="Pakistan" <?php if ($currentregion == 'Pakistan') echo 'selected'; ?>>Pakistan</option>
                  <option value="Palau" <?php if ($currentregion == 'Palau') echo 'selected'; ?>>Palau</option>
                  <option value="Palestine State" <?php if ($currentregion == 'Palestine State') echo 'selected'; ?>>Palestine State</option>
                  <option value="Panama" <?php if ($currentregion == 'Panama') echo 'selected'; ?>>Panama</option>
                  <option value="Papua New Guinea" <?php if ($currentregion == 'Papua New Guinea') echo 'selected'; ?>>Papua New Guinea</option>
                  <option value="Paraguay" <?php if ($currentregion == 'Paraguay') echo 'selected'; ?>>Paraguay</option>
                  <option value="Peru" <?php if ($currentregion == 'Peru') echo 'selected'; ?>>Peru</option>
                  <option value="Philippines" <?php if ($currentregion == 'Philippines') echo 'selected'; ?>>Philippines</option>
                  <option value="Poland" <?php if ($currentregion == 'Poland') echo 'selected'; ?>>Poland</option>
                  <option value="Portugal" <?php if ($currentregion == 'Portugal') echo 'selected'; ?>>Portugal</option>
                  <option value="Qatar" <?php if ($currentregion == 'Qatar') echo 'selected'; ?>>Qatar</option>
                  <option value="Romania" <?php if ($currentregion == 'Romania') echo 'selected'; ?>>Romania</option>
                  <option value="Russia" <?php if ($currentregion == 'Russia') echo 'selected'; ?>>Russia</option>
                  <option value="Rwanda" <?php if ($currentregion == 'Rwanda') echo 'selected'; ?>>Rwanda</option>
                  <option value="Saint Kitts and Nevis" <?php if ($currentregion == 'Saint Kitts and Nevis') echo 'selected'; ?>>Saint Kitts and Nevis</option>
                  <option value="Saint Lucia" <?php if ($currentregion == 'Saint Lucia') echo 'selected'; ?>>Saint Lucia</option>
                  <option value="Saint Vincent and the Grenadines" <?php if ($currentregion == 'Saint Vincent and the Grenadines') echo 'selected'; ?>>Saint Vincent and the Grenadines</option>
                  <option value="Samoa" <?php if ($currentregion == 'Samoa') echo 'selected'; ?>>Samoa</option>
                  <option value="San Marino" <?php if ($currentregion == 'San Marino') echo 'selected'; ?>>San Marino</option>
                  <option value="Sao Tome and Principe" <?php if ($currentregion == 'Sao Tome and Principe') echo 'selected'; ?>>Sao Tome and Principe</option>
                  <option value="Saudi Arabia" <?php if ($currentregion == 'Saudi Arabia') echo 'selected'; ?>>Saudi Arabia</option>
                  <option value="Senegal" <?php if ($currentregion == 'Senegal') echo 'selected'; ?>>Senegal</option>
                  <option value="Serbia" <?php if ($currentregion == 'Serbia') echo 'selected'; ?>>Serbia</option>
                  <option value="Seychelles" <?php if ($currentregion == 'Seychelles') echo 'selected'; ?>>Seychelles</option>
                  <option value="Sierra Leone" <?php if ($currentregion == 'Sierra Leone') echo 'selected'; ?>>Sierra Leone</option>
                  <option value="Singapore" <?php if ($currentregion == 'Singapore') echo 'selected'; ?>>Singapore</option>
                  <option value="Slovakia" <?php if ($currentregion == 'Slovakia') echo 'selected'; ?>>Slovakia</option>
                  <option value="Slovenia" <?php if ($currentregion == 'Slovenia') echo 'selected'; ?>>Slovenia</option>
                  <option value="Solomon Islands" <?php if ($currentregion == 'Solomon Islands') echo 'selected'; ?>>Solomon Islands</option>
                  <option value="Somalia" <?php if ($currentregion == 'Somalia') echo 'selected'; ?>>Somalia</option>
                  <option value="South Africa" <?php if ($currentregion == 'South Africa') echo 'selected'; ?>>South Africa</option>
                  <option value="South Korea" <?php if ($currentregion == 'South Korea') echo 'selected'; ?>>South Korea</option>
                  <option value="South Sudan" <?php if ($currentregion == 'South Sudan') echo 'selected'; ?>>South Sudan</option>
                  <option value="Spain" <?php if ($currentregion == 'Spain') echo 'selected'; ?>>Spain</option>
                  <option value="Sri Lanka" <?php if ($currentregion == 'Sri Lanka') echo 'selected'; ?>>Sri Lanka</option>
                  <option value="Sudan" <?php if ($currentregion == 'Sudan') echo 'selected'; ?>>Sudan</option>
                  <option value="Suriname" <?php if ($currentregion == 'Suriname') echo 'selected'; ?>>Suriname</option>
                  <option value="Sweden" <?php if ($currentregion == 'Sweden') echo 'selected'; ?>>Sweden</option>
                  <option value="Switzerland" <?php if ($currentregion == 'Switzerland') echo 'selected'; ?>>Switzerland</option>
                  <option value="Syria" <?php if ($currentregion == 'Syria') echo 'selected'; ?>>Syria</option>
                  <option value="Tajikistan" <?php if ($currentregion == 'Tajikistan') echo 'selected'; ?>>Tajikistan</option>
                  <option value="Tanzania" <?php if ($currentregion == 'Tanzania') echo 'selected'; ?>>Tanzania</option>
                  <option value="Thailand" <?php if ($currentregion == 'Thailand') echo 'selected'; ?>>Thailand</option>
                  <option value="Togo" <?php if ($currentregion == 'Togo') echo 'selected'; ?>>Togo</option>
                  <option value="Tonga" <?php if ($currentregion == 'Tonga') echo 'selected'; ?>>Tonga</option>
                  <option value="Trinidad and Tobago" <?php if ($currentregion == 'Trinidad and Tobago') echo 'selected'; ?>>Trinidad and Tobago</option>
                  <option value="Tunisia" <?php if ($currentregion == 'Tunisia') echo 'selected'; ?>>Tunisia</option>
                  <option value="Turkey" <?php if ($currentregion == 'Turkey') echo 'selected'; ?>>Turkey</option>
                  <option value="Turkmenistan" <?php if ($currentregion == 'Turkmenistan') echo 'selected'; ?>>Turkmenistan</option>
                  <option value="Tuvalu" <?php if ($currentregion == 'Tuvalu') echo 'selected'; ?>>Tuvalu</option>
                  <option value="Uganda" <?php if ($currentregion == 'Uganda') echo 'selected'; ?>>Uganda</option>
                  <option value="Ukraine" <?php if ($currentregion == 'Ukraine') echo 'selected'; ?>>Ukraine</option>
                  <option value="United Arab Emirates" <?php if ($currentregion == 'United Arab Emirates') echo 'selected'; ?>>United Arab Emirates</option>
                  <option value="United Kingdom" <?php if ($currentregion == 'United Kingdom') echo 'selected'; ?>>United Kingdom</option>
                  <option value="United States" <?php if ($currentregion == 'United States') echo 'selected'; ?>>United States</option>
                  <option value="Uruguay" <?php if ($currentregion == 'Uruguay') echo 'selected'; ?>>Uruguay</option>
                  <option value="Uzbekistan" <?php if ($currentregion == 'Uzbekistan') echo 'selected'; ?>>Uzbekistan</option>
                  <option value="Vanuatu" <?php if ($currentregion == 'Vanuatu') echo 'selected'; ?>>Vanuatu</option>
                  <option value="Vatican City" <?php if ($currentregion == 'Vatican City') echo 'selected'; ?>>Vatican City</option>
                  <option value="Venezuela" <?php if ($currentregion == 'Venezuela') echo 'selected'; ?>>Venezuela</option>
                  <option value="Vietnam" <?php if ($currentregion == 'Vietnam') echo 'selected'; ?>>Vietnam</option>
                  <option value="Yemen" <?php if ($currentregion == 'Yemen') echo 'selected'; ?>>Yemen</option>
                  <option value="Zambia" <?php if ($currentregion == 'Zambia') echo 'selected'; ?>>Zambia</option>
                  <option value="Zimbabwe" <?php if ($currentregion == 'Zimbabwe') echo 'selected'; ?>>Zimbabwe</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      <?php include('end.php'); ?> 
    </main>