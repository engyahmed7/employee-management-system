<?php
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset ($_POST['name']) ? $_POST['name'] : '';
    $email = isset ($_POST['email']) ? $_POST['email'] : '';
    $phones = isset ($_POST['phones']) ? $_POST['phones'] : '';
    $addresses = isset ($_POST['address']) ? $_POST['address'] : '';

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

    function updateEmployee($name, $email, $phones, $addresses){
        global $xml;
        $root = initDOM();
        $employees = $root->getElementsByTagName('employee');
        $allEmployees = [];
        foreach ($employees as $employee) {
            $empDetails = [];
            foreach ($employee->childNodes as $node) {
                if ($node->nodeType === XML_ELEMENT_NODE) {
                    if ($node->nodeName === 'phones') {
                        $phonesArray = [];
                        foreach ($node->childNodes as $phoneNode) {
                            if ($phoneNode->nodeType === XML_ELEMENT_NODE && $phoneNode->nodeName === 'phone') {
                                $phoneDetails = [
                                    'number' => $phoneNode->nodeValue,
                                    'type' => $phoneNode->getAttribute('Type')
                                ];
                                $phonesArray[] = $phoneDetails;
                            }
                        }
                        $empDetails['phones'] = $phonesArray;
                    }
                    elseif ($node->nodeName === 'addresses') {
                        $addressesArray = [];
                        foreach ($node->childNodes as $addressNode) {
                            if ($addressNode->nodeType === XML_ELEMENT_NODE && $addressNode->nodeName === 'address') {
                                $addressDetails = [
                                    'street' => $addressNode->getElementsByTagName('Street')[0]->nodeValue,
                                    'building' => $addressNode->getElementsByTagName('BuildingNumber')[0]->nodeValue,
                                    'region' => $addressNode->getElementsByTagName('Region')[0]->nodeValue,
                                    'city' => $addressNode->getElementsByTagName('City')[0]->nodeValue,
                                    'country' => $addressNode->getElementsByTagName('Country')[0]->nodeValue
                                ];
                                $addressesArray[] = $addressDetails;
                            }
                        }
                        $empDetails['addresses'] = $addressesArray;
                    }
                    else {
                        $empDetails[$node->nodeName] = $node->nodeValue;
                    }
                }
            }
            
            $allEmployees[] = $empDetails;
        }
        return $allEmployees;
    }
    

}

$disableButton = false;
$disablePrevButton = false;

function getAllEmps(){
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
                }
                elseif ($node->nodeName === 'addresses') {
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
                }
                else {
                    $empDetails[$node->nodeName] = $node->nodeValue;
                }
            }
        }
        
        $allEmployees[] = $empDetails;
    }

    return $allEmployees;
}

function nextRecord(){
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

function prevRecord() {
    global $disablePrevButton, $currentEmp;
    if (!isset($_SESSION['i'])) {
        $_SESSION['i'] = 0;
    }

    $allEmployees = getAllEmps();

    $array_size = count($allEmployees);

    if ($array_size > 0 && $_SESSION['i'] > 0) {
        $_SESSION['i']--; 
        $i = $_SESSION['i'];
        $currentEmp = $allEmployees[$i];
        return $currentEmp;
    } else {
        $disablePrevButton = true;
        return null;
    }
}




function checkEmpty($name, $email, $phones, $addresses)
{
    if (empty ($name) || empty ($email) || empty ($phones) || empty ($addresses)) {
        $_SESSION['error'] = 'Please fill all fields';
        return true;
    }
    return false;
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
}elseif(isset ($_POST['update'])){

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


        <h1 class="display-4 mb-5 text-center">Employees</h1>
        <form class="row g-3" method="post">
            <!-- name -->
            <div class="col-md-12">
                <label for="inputName4" class="form-label">Name</label>
                <input type="text" class="form-control" name="name" value="<?= isset($currentEmp['name'])?$currentEmp['name']:'' ?>" id="inputName4" placeholder="name" />
            </div>
            <!-- phone -->
            <div class="row mt-3">
                <label for="inputPhone" class="form-label">Phone</label>
                <div class="col-md-6">
                    <input type="text" class="form-control" value="<?= isset($currentEmp['phones'][0]['number'])?$currentEmp['phones'][0]['number']:'' ?>" name="phones[0][number]" placeholder="Phone Number" />
                    <select class="form-select mt-2" name="phones[0][type]">
                        <option value="home" <?php echo (isset($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'home') ? 'selected' : ''; ?>>Home</option>
                        <option value="work" <?php echo (isset($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'work') ? 'selected' : ''; ?>>Work</option>
                        <option value="mobile" <?php echo (isset($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'mobile') ? 'selected' : ''; ?>>Mobile</option>
                    </select>

                </div>

                <div class="col-md-6">
                    <input type="text" class="form-control" value="<?= isset($currentEmp['phones'][0]['number'])?$currentEmp['phones'][1]['number']:'' ?>" name="phones[1][number]" placeholder="Phone Number" />
                    <select class="form-select mt-2" name="phones[0][type]">
                        <option value="home" <?php echo (isset($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'home') ? 'selected' : ''; ?>>Home</option>
                        <option value="work" <?php echo (isset($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'work') ? 'selected' : ''; ?>>Work</option>
                        <option value="mobile" <?php echo (isset($currentEmp['phones'][1]['type']) && $currentEmp['phones'][1]['type'] === 'mobile') ? 'selected' : ''; ?>>Mobile</option>
                    </select>


                </div>
            </div>


            <!-- email -->
            <div class="col-md-12 mt-3">
                <label for="inputEmail4" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?= isset($currentEmp['email'])?$currentEmp['email']:'' ?>"  id="inputEmail4" placeholder="email" />
            </div>

            <!-- address -->
            <div class="row mt-4">
                <label for="inputAddress" class="form-label">Address</label>
                <div class="col-md-2">
                    <input type="text" class="form-control" value="<?= isset($currentEmp['addresses'][0]['street'])?$currentEmp['addresses'][0]['street']:'' ?>" name="address[0][street]" id="inputAddress"
                        placeholder="Street" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" value="<?= isset($currentEmp['addresses'][0]['building'])?$currentEmp['addresses'][0]['building']:'' ?>" name="address[0][building]" id="inputAddress2"
                        placeholder="Building Number" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" value="<?= isset($currentEmp['addresses'][0]['region'])?$currentEmp['addresses'][0]['region']:'' ?>"  name="address[0][region]" id="inputAddress3"
                        placeholder="Region" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" value="<?= isset($currentEmp['addresses'][0]['city'])?$currentEmp['addresses'][0]['city']:'' ?>" name="address[0][city]" id="inputAddress4"
                        placeholder="City" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" value="<?= isset($currentEmp['addresses'][0]['country'])?$currentEmp['addresses'][0]['country']:'' ?>" name="address[0][country]" id="inputAddress5"
                        placeholder="Country" />
                </div>
            </div>

            <div class="col-12 text-center mt-5 mb-3">
                <button type="submit" name="prev" class="btn btn-outline-dark me-3" <?php echo $disablePrevButton? 'disabled': '' ?> >
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
                <button type="submit" name="next" class="btn btn-outline-dark me-3" <?php echo $disableButton? 'disabled': '' ?>>
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