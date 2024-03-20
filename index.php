<?php
session_start();

$currentEmp = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset ($_POST['name']) ? $_POST['name'] : '';
    $email = isset ($_POST['email']) ? $_POST['email'] : '';
    $phones = isset ($_POST['phones']) ? $_POST['phones'] : [];
    $addresses = isset ($_POST['address']) ? $_POST['address'] : [];

    $oldname = isset ($_POST['oldname']) ? $_POST['oldname'] : '';

    
}

function initDOM()
{
    global $xml;
    $xml = new DOMDocument("1.0", "UTF-8");
    $xml->load('data/employees.xml');
    return $xml->documentElement;
}

function insertEmp($name, $email, $phones, $addresses)
{
    global $xml;
    $root = initDOM();

    $employee = $xml->createElement("employee");
    $employee->appendChild($xml->createElement("name", $name));
    $employee->appendChild($xml->createElement("email", $email));

    $phonesElement = $xml->createElement("phones");
    foreach ($phones as $phone) {
        $phoneElement = $xml->createElement("phone", $phone['number']);
        $phoneElement->setAttribute("Type", $phone['type']);
        $phonesElement->appendChild($phoneElement);
    }
    $employee->appendChild($phonesElement);

    $addressesElement = $xml->createElement("addresses");
    foreach ($addresses as $address) {
        $addressElement = $xml->createElement("address");
        $addressElement->appendChild($xml->createElement("Street", $address['street']));
        $addressElement->appendChild($xml->createElement("BuildingNumber", $address['building']));
        $addressElement->appendChild($xml->createElement("Region", $address['region']));
        $addressElement->appendChild($xml->createElement("City", $address['city']));
        $addressElement->appendChild($xml->createElement("Country", $address['country']));
        $addressesElement->appendChild($addressElement);
    }
    $employee->appendChild($addressesElement);

    $root->appendChild($employee);
    $xml->save('data/employees.xml');

    $_SESSION['success'] = 'Employee added successfully';
    header('Location: index.php');
    exit;
}
$disableButton = false;
$disablePrevButton = false;



function getAllEmps()
{
    $root = initDOM();
    $employees = $root->getElementsByTagName('employee');
    $allEmployees = [];

    foreach ($employees as $employee) {
        $empDetails = [];
        foreach ($employee->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                if ($node->nodeName === 'phones') {
                    $phones = [];
                    foreach ($node->childNodes as $phoneNode) {
                        if ($phoneNode->nodeType === XML_ELEMENT_NODE && $phoneNode->nodeName === 'phone') {
                            $phoneDetails = [
                                'number' => $phoneNode->nodeValue,
                                'type' => $phoneNode->getAttribute('Type')
                            ];
                            $phones[] = $phoneDetails;
                        }
                    }
                    $empDetails['phones'] = $phones;
                } elseif ($node->nodeName === 'addresses') {
                    $addresses = [];
                    foreach ($node->childNodes as $addressNode) {
                        if ($addressNode->nodeType === XML_ELEMENT_NODE && $addressNode->nodeName === 'address') {
                            $addressDetails = [
                                'street' => $addressNode->getElementsByTagName('Street')[0]->nodeValue,
                                'building' => $addressNode->getElementsByTagName('BuildingNumber')[0]->nodeValue,
                                'region' => $addressNode->getElementsByTagName('Region')[0]->nodeValue,
                                'city' => $addressNode->getElementsByTagName('City')[0]->nodeValue,
                                'country' => $addressNode->getElementsByTagName('Country')[0]->nodeValue
                            ];
                            $addresses[] = $addressDetails;
                        }
                    }
                    $empDetails['addresses'] = $addresses;
                } else {
                    $empDetails[$node->nodeName] = $node->nodeValue;
                }
            }
        }

        $allEmployees[] = $empDetails;
    }

    return $allEmployees;
}

function nextRecord()
{
    global $currentEmp;
    global $disableButton;
    if (!isset ($_SESSION['i'])) {
        $_SESSION['i'] = 0;
    }

    $allEmployees = getAllEmps();

    $array_size = count($allEmployees);

    if ($array_size > 0 && $_SESSION['i'] < $array_size) {
        $i = $_SESSION['i'];
        $i = ($i >= $array_size) ? 0 : $i;
        $currentEmp = $allEmployees[$i];
        $_SESSION['i']++;
        if ($_SESSION['i'] >= $array_size) {
            $_SESSION['i'] = 2;
            $disableButton = true;
        }
        return $currentEmp;
    } else {
        return null;
    }
}

function prevRecord()
{
    global $disablePrevButton, $currentEmp;
    if (!isset ($_SESSION['i'])) {
        $_SESSION['i'] = 0;
    }

    $allEmployees = getAllEmps();

    $array_size = count($allEmployees);

    if ($array_size > 0 && $_SESSION['i'] > -1) {
        $i = $_SESSION['i'];
        $currentEmp = $allEmployees[$i];
        $_SESSION['i']--;
        if($_SESSION['i'] === -1){
            $_SESSION['i'] = 0;
            $disablePrevButton = true;
        }
        return $currentEmp;
    } else {
        $disablePrevButton = true;
        return null;
    }
}

function updateEmployee($oldname, $name, $email, $phones, $addresses)
{
    $xml = new DOMDocument("1.0", "UTF-8");
    $xml->load('data/employees.xml');
    $root = $xml->documentElement;

    $allEmployees = getAllEmps();
    $indexToUpdate = -1;
    foreach ($allEmployees as $index => $employee) {
        if ($employee['name'] === $oldname) {
            $indexToUpdate = $index;
            break;
        }
    }

    if ($indexToUpdate !== -1) {

        $allEmployees[$indexToUpdate]['name'] = $name;
        $allEmployees[$indexToUpdate]['email'] = $email;
        foreach ($allEmployees[$indexToUpdate]['phones'] as $index => $phone) {
            if (isset($phones[$index])) {
                $allEmployees[$indexToUpdate]['phones'][$index]['number'] = $phones[$index]['number'];
                $allEmployees[$indexToUpdate]['phones'][$index]['type'] = $phones[$index]['type'];
            }
        }
        foreach ($allEmployees[$indexToUpdate]['addresses'] as $index => $address) {
            if (isset($addresses[$index])) {
                $allEmployees[$indexToUpdate]['addresses'][$index]['street'] = $addresses[$index]['street'];
                $allEmployees[$indexToUpdate]['addresses'][$index]['building'] = $addresses[$index]['building'];
                $allEmployees[$indexToUpdate]['addresses'][$index]['region'] = $addresses[$index]['region'];
                $allEmployees[$indexToUpdate]['addresses'][$index]['city'] = $addresses[$index]['city'];
                $allEmployees[$indexToUpdate]['addresses'][$index]['country'] = $addresses[$index]['country'];
            }
        }
    }

    $newXml = new DOMDocument("1.0", "UTF-8");
    $newXml->appendChild($newXml->createElement("employees"));

    foreach ($allEmployees as $employee) {
        $employeeElement = $newXml->createElement("employee");
        $employeeElement->appendChild($newXml->createElement("name", $employee['name']));
        $employeeElement->appendChild($newXml->createElement("email", $employee['email']));

        $phonesElement = $newXml->createElement("phones");
        foreach ($employee['phones'] as $phone) {
            $phoneElement = $newXml->createElement("phone", $phone['number']);
            $phoneElement->setAttribute("Type", $phone['type']);
            $phonesElement->appendChild($phoneElement);
        }
        $employeeElement->appendChild($phonesElement);

        $addressesElement = $newXml->createElement("addresses");
        foreach ($employee['addresses'] as $address) {
            $addressElement = $newXml->createElement("address");
            $addressElement->appendChild($newXml->createElement("Street", $address['street']));
            $addressElement->appendChild($newXml->createElement("BuildingNumber", $address['building']));
            $addressElement->appendChild($newXml->createElement("Region", $address['region']));
            $addressElement->appendChild($newXml->createElement("City", $address['city']));
            $addressElement->appendChild($newXml->createElement("Country", $address['country']));
            $addressesElement->appendChild($addressElement);
        }
        $employeeElement->appendChild($addressesElement);

        $newXml->documentElement->appendChild($employeeElement);
    }

    // Save the updated data to the XML file
    $newXml->save('data/employees.xml');
    $_SESSION['success'] = 'Employee updated successfully';
    header('Location: index.php');
    exit;
}

function deleteEmployee($name)
{
    $xml = new DOMDocument("1.0", "UTF-8");
    $xml->load('data/employees.xml');
    $root = $xml->documentElement;

    $allEmployees = getAllEmps();
    $indexToDelete = -1;
    foreach ($allEmployees as $index => $employee) {
        if ($employee['name'] === $name) {
            $indexToDelete = $index;
            break;
        }
    }

    if ($indexToDelete !== -1) {
        unset($allEmployees[$indexToDelete]);
    }

    $newXml = new DOMDocument("1.0", "UTF-8");
    $newXml->appendChild($newXml->createElement("employees"));

    foreach ($allEmployees as $employee) {
        $employeeElement = $newXml->createElement("employee");
        $employeeElement->appendChild($newXml->createElement("name", $employee['name']));
        $employeeElement->appendChild($newXml->createElement("email", $employee['email']));

        $phonesElement = $newXml->createElement("phones");
        foreach ($employee['phones'] as $phone) {
            $phoneElement = $newXml->createElement("phone", $phone['number']);
            $phoneElement->setAttribute("Type", $phone['type']);
            $phonesElement->appendChild($phoneElement);
        }
        $employeeElement->appendChild($phonesElement);

        $addressesElement = $newXml->createElement("addresses");
        foreach ($employee['addresses'] as $address) {
            $addressElement = $newXml->createElement("address");
            $addressElement->appendChild($newXml->createElement("Street", $address['street']));
            $addressElement->appendChild($newXml->createElement("BuildingNumber", $address['building']));
            $addressElement->appendChild($newXml->createElement("Region", $address['region']));
            $addressElement->appendChild($newXml->createElement("City", $address['city']));
            $addressElement->appendChild($newXml->createElement("Country", $address['country']));
            $addressesElement->appendChild($addressElement);
        }
        $employeeElement->appendChild($addressesElement);

        $newXml->documentElement->appendChild($employeeElement);
    }

    $newXml->save('data/employees.xml');
    $_SESSION['success'] = 'Employee deleted sucessfully';

}




function checkEmpty($name, $email, $phones, $addresses)
{
    if (empty ($name) || empty ($email) || empty ($phones) || empty ($addresses)) {
        $_SESSION['error'] = 'Please fill all fields';
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchRes = isset($_GET['search']) ? $_GET['search']:'';
    
}

function search(){
    global $searchRes;
    $root = initDOM();

    $allEmployees = getAllEmps();
    $searchedEmp=[];
    foreach ($allEmployees as $emp) {
        if($emp['name'] === $searchRes){
            $searchedEmp=$emp;
        }  
    }
    // print_r($searchedEmp);
    return $searchedEmp;
}


if (isset ($_POST['insert'])) {
    if (checkEmpty($name, $email, $phones, $addresses)) {
        header('Location: index.php');
        exit;
    }
    insertEmp($name, $email, $phones, $addresses);
} elseif (isset ($_POST['prev'])) {
    prevRecord();
} elseif (isset ($_POST['next'])) {
    nextRecord();
} elseif (isset ($_POST['update'])) {
    if (checkEmpty($name, $email, $phones, $addresses)) {
        header('Location: index.php');
        exit;
    }
    updateEmployee($oldname, $name, $email, $phones, $addresses);
} elseif (isset ($_POST['delete'])) {
    deleteEmployee($name);
}elseif(isset($_GET['searchBtn'])){
    search();
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="css/style.css">
</head>

<body>


    <div class="container mt-3 card">
        <?php if (isset ($_SESSION['success'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

                <strong>
                    <?= $_SESSION['success'] ?>
                </strong>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php } ?>

        <?php if (isset ($_SESSION['error'])) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

                <strong>
                    <?= $_SESSION['error'] ?>
                </strong>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php } ?>

        <?php  if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['searchBtn'])){
            $searchedEmp = search();
            if(count($searchedEmp) > 0){
                $currentEmp = $searchedEmp;
            }else{
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                <strong>Employee not found</strong>
            </div>";
            }
        }
        ?>

        <h1 class="display-4 mb-2 text-center">Employees</h1>
        <form class="row g-3 m-auto" method="get" >
            <div class="col-10">
                <input type='text' name='search' class="form-control" />
            </div>
            <div class="col-2">
                <button type="submit" name="searchBtn" class="btn btn-outline-success me-3">
                        search
                </button>
                
            </div>
        </form>
        <form class="row g-3" method="post">
            <input type="hidden" name="oldname" value="<?= isset ($currentEmp['name']) ? htmlspecialchars($currentEmp['name']) : '' ?>">
            <!-- name -->
            <div class="col-md-12">
                <label for="inputName4" class="form-label">Name</label>
                <input type="text" class="form-control" name="name"
                    value="<?= isset ($currentEmp['name']) ? $currentEmp['name'] : '' ?>" id="inputName4"
                    placeholder="name" />
            </div>
            <!-- phone -->
            <div class="row mt-3">
                <label for="inputPhone" class="form-label">Phone</label>
                <div class="col-md-6">
                    <input type="text" class="form-control"
                        value="<?= isset ($currentEmp['phones'][0]['number']) ? $currentEmp['phones'][0]['number'] : '' ?>"
                        name="phones[0][number]" placeholder="Phone Number" />
                    <select class="form-select mt-2" name="phones[0][type]">
                        <option value="home" <?php echo (isset ($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'home') ? 'selected' : ''; ?>>Home</option>
                        <option value="work" <?php echo (isset ($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'work') ? 'selected' : ''; ?>>Work</option>
                        <option value="mobile" <?php echo (isset ($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'mobile') ? 'selected' : ''; ?>>Mobile</option>
                    </select>

                </div>

                <div class="col-md-6">
                    <input type="text" class="form-control"
                        value="<?= isset ($currentEmp['phones'][0]['number']) ? $currentEmp['phones'][1]['number'] : '' ?>"
                        name="phones[1][number]" placeholder="Phone Number" />
                    <select class="form-select mt-2" name="phones[1][type]">
                        <option value="home" <?php echo (isset ($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'home') ? 'selected' : ''; ?>>Home</option>
                        <option value="work" <?php echo (isset ($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'work') ? 'selected' : ''; ?>>Work</option>
                        <option value="mobile" <?php echo (isset ($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'mobile') ? 'selected' : ''; ?>>Mobile</option>
                    </select>


                </div>
            </div>


            <!-- email -->
            <div class="col-md-12 mt-3">
                <label for="inputEmail4" class="form-label">Email</label>
                <input type="email" class="form-control" name="email"
                    value="<?= isset ($currentEmp['email']) ? $currentEmp['email'] : '' ?>" id="inputEmail4"
                    placeholder="email" />
            </div>

            <!-- address -->
            <div class="row mt-4">
                <label for="inputAddress" class="form-label">Address</label>
                <div class="col-md-2">
                    <input type="text" class="form-control"
                        value="<?= isset ($currentEmp['addresses'][0]['street']) ? $currentEmp['addresses'][0]['street'] : '' ?>"
                        name="address[0][street]" id="inputAddress" placeholder="Street" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control"
                        value="<?= isset ($currentEmp['addresses'][0]['building']) ? $currentEmp['addresses'][0]['building'] : '' ?>"
                        name="address[0][building]" id="inputAddress2" placeholder="Building Number" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control"
                        value="<?= isset ($currentEmp['addresses'][0]['region']) ? $currentEmp['addresses'][0]['region'] : '' ?>"
                        name="address[0][region]" id="inputAddress3" placeholder="Region" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control"
                        value="<?= isset ($currentEmp['addresses'][0]['city']) ? $currentEmp['addresses'][0]['city'] : '' ?>"
                        name="address[0][city]" id="inputAddress4" placeholder="City" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control"
                        value="<?= isset ($currentEmp['addresses'][0]['country']) ? $currentEmp['addresses'][0]['country'] : '' ?>"
                        name="address[0][country]" id="inputAddress5" placeholder="Country" />
                </div>
            </div>

            <div class="col-12 text-center mt-5 mb-3">
                <button type="submit" name="prev" class="btn btn-outline-dark me-3" <?php echo $disablePrevButton ? 'disabled' : '' ?>>
                    Prev
                </button>
                <button type="submit" name="insert" class="btn btn-success me-3">
                    Insert
                </button>

                <button type="submit" name="update" class="btn btn-warning me-3">
                    Update
                </button>
                <button type="submit" name="delete" class="btn btn-danger me-3">
                    Delete
                </button>
                <button type="submit" name="next" class="btn btn-outline-dark me-3" <?php echo $disableButton ? 'disabled' : '' ?>>
                    Next
                </button>
            </div>
        </form>
        <a href="pages/displayeEmp.php" class="btn btn-outline-primary mt-1 w-50 m-auto"> Display Employees </a>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>