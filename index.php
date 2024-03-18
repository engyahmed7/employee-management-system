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


    <div class="container mt-5 card">
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
                <input type="text" class="form-control" name="name" id="inputName4" placeholder="name" />
            </div>
            <!-- phone -->
            <div class="row mt-3">
                <label for="inputPhone" class="form-label">Phone</label>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="phones[0][number]" placeholder="Phone Number" />
                    <select class="form-select mt-2" name="phones[0][type]">
                        <option value="home">Home</option>
                        <option value="work">Work</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <input type="text" class="form-control" name="phones[1][number]" placeholder="Phone Number" />
                    <select class="form-select mt-2" name="phones[1][type]">
                        <option value="home">Home</option>
                        <option value="work">Work</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>
            </div>


            <!-- email -->
            <div class="col-md-12 mt-3">
                <label for="inputEmail4" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="inputEmail4" placeholder="email" />
            </div>

            <!-- address -->
            <div class="row mt-4">
                <label for="inputAddress" class="form-label">Address</label>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="address[0][street]" id="inputAddress"
                        placeholder="Street" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="address[0][building]" id="inputAddress2"
                        placeholder="Building Number" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="address[0][region]" id="inputAddress3"
                        placeholder="Region" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="address[0][city]" id="inputAddress4"
                        placeholder="City" />
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="address[0][country]" id="inputAddress5"
                        placeholder="Country" />
                </div>
            </div>

            <div class="col-12 text-center mt-5 mb-3">
                <button type="submit" name="insert" class="btn btn-primary me-3">
                    Insert
                </button>

                <button type="submit" name="update" class="btn btn-primary me-3">
                    Update
                </button>
                <button type="submit" name="delete" class="btn btn-primary me-3">
                    Delete
                </button>
            </div>
        </form>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>