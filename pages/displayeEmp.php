<?php

function getEmp()
{
    $employees = simplexml_load_file('../data/employees.xml');
    $employeeData = [];

    foreach ($employees as $employee) {
        $name = $employee->name;
        $phones = [];
        $addresses = [];

        foreach ($employee->phones->phone as $phone) {
            $phones[] = $phone;
        }

        foreach ($employee->addresses->address as $address) {
            $addressDetails = [
                'street' => $address->Street,
                'buildingNumber' => $address->BuildingNumber,
                'region' => $address->Region,
                'city' => $address->City,
                'country' => $address->Country
            ];
            $addresses[] = $addressDetails;
        }


        $employeeData[] = [
            'name' => $name,
            'phones' => $phones,
            'addresses' => $addresses
        ];
    }

    return $employeeData;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>
    <div class="container">
        <p class="display-5 text-center mt-5 mb-3">All Employees</p>
        <div class="table-responsive">
            <table class="table table-striped mt-5" style="box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Phones</th>
                        <th scope="col">Addresses</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $emps = getEmp();
                    foreach ($emps as $emp) {
                        ?>
                        <tr>
                            <td class="lead">
                                <?= $emp['name'] ?>
                            </td>
                            <td>
                                <ul class="list-unstyled">
                                    <?php foreach ($emp['phones'] as $phone) { ?>
                                        <li class="lead">
                                            <?= $phone ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                            <td>
                                <ul class="list-unstyled">
                                    <?php foreach ($emp['addresses'] as $address) { ?>
                                        <li class="lead">
                                            <?= $address['street'] ?>,
                                            <?= $address['buildingNumber'] ?>,
                                            <?= $address['region'] ?>,
                                            <?= $address['city'] ?>,
                                            <?= $address['country'] ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>