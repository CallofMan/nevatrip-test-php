<?php
function createOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity) {
    $mysqli = new mysqli("localhost", "root", "", "nevatrip-test-1");
    if ($mysqli->connect_errno) {
        die("Ошибка подключения: " . $mysqli->connect_error);
    }

    // Проверка на уникальность event_id
    $stmt = $mysqli->prepare("SELECT id FROM nevatrip_order WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return "Заказ с таким event_id уже существует!";
    }
    $stmt->close();

    // Генерация уникального штрихкода
    $barcode = generateUniqueBarcode($mysqli);

    // Данные для бронирования
    $orderData = [
        'event_id' => $event_id,
        'event_date' => $event_date,
        'ticket_adult_price' => $ticket_adult_price,
        'ticket_adult_quantity' => $ticket_adult_quantity,
        'ticket_kid_price' => $ticket_kid_price,
        'ticket_kid_quantity' => $ticket_kid_quantity,
        'barcode' => $barcode,
    ];

    // Попытка бронирования
    while (true) {
        $bookingResponse = mockBookingAPI($orderData);

        if (isset($bookingResponse['message']) && $bookingResponse['message'] === 'order successfully booked') {
            break;
        } elseif (isset($bookingResponse['error']) && $bookingResponse['error'] === 'barcode already exists') {
            $barcode = generateUniqueBarcode($mysqli);
            $orderData['barcode'] = $barcode;
        } else {
            $mysqli->close();
            return "Ошибка при бронировании: " . $bookingResponse['error'];
        }
    }

    // Подтверждение бронирования
    $approvalResponse = mockApprovalAPI(['barcode' => $barcode]);
    if (isset($approvalResponse['message']) && $approvalResponse['message'] === 'order successfully approved') {
        $equal_price = ($ticket_adult_price * $ticket_adult_quantity) + ($ticket_kid_price * $ticket_kid_quantity);
        
        // Сохранение заказа в БД
        $stmt = $mysqli->prepare("INSERT INTO nevatrip_order (event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode, equal_price, created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isiiisis", $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode, $equal_price);
        $stmt->execute();
        $stmt->close();
    } else {
        $mysqli->close();
        return "Ошибка подтверждения заказа: " . $approvalResponse['error'];
    }

    $mysqli->close();
    return "Заказ успешно создан с штрихкодом: " . $barcode;
}

function generateUniqueBarcode($mysqli) {
    do {
        $barcode = strval(random_int(10000000, 99999999));
        $result = $mysqli->query("SELECT id FROM nevatrip_order WHERE barcode = '$barcode'");
    } while ($result->num_rows > 0);

    return $barcode;
}

function mockBookingAPI($data) {
    $responses = [
        ['message' => 'order successfully booked'],
        ['error' => 'barcode already exists']
    ];
    return $responses[array_rand($responses)];
}

function mockApprovalAPI($data) {
    $responses = [
        ['message' => 'order successfully approved'],
        ['error' => 'event cancelled'],
        ['error' => 'no tickets'],
        ['error' => 'no seats'],
        ['error' => 'fan removed']
    ];
    return $responses[array_rand($responses)];
}

echo createOrder(1, '2024-08-21', 500, 2, 300, 3);
?>