<?php

require_once "../backend/db.php";

/* Get visit requests */

$search = $_GET["search"] ?? "";

if (!empty($search)) {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT * FROM visits
         WHERE name LIKE ?
         OR phone LIKE ?
         OR email LIKE ?
         ORDER BY id DESC"
    );

    $stmt->bind_param(
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $sql = "SELECT * FROM visits ORDER BY id DESC";
    $result = $conn->query($sql);

}

/* CRM statistics */
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM visits");
$totalLeads = $totalResult->fetch_assoc()["total"];

$newResult = $conn->query("SELECT COUNT(*) AS total FROM visits WHERE status = 'New'");
$newLeads = $newResult->fetch_assoc()["total"];

$contactedResult = $conn->query("SELECT COUNT(*) AS total FROM visits WHERE status = 'Contacted'");
$contactedLeads = $contactedResult->fetch_assoc()["total"];

$closedResult = $conn->query("SELECT COUNT(*) AS total FROM visits WHERE status = 'Closed'");
$closedLeads = $closedResult->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Groving Soil CRM</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f0e8;
            color: #18251c;
        }

        .header {
            background: #18251c;
            color: white;
            padding: 25px 35px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin: 7px 0 0;
            opacity: 0.8;
        }

        .container {
            max-width: 1400px;
            margin: auto;
            padding: 30px;
        }

        /* KPI CARDS */

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .stat-card h3 {
            margin: 0 0 10px;
            font-size: 15px;
            color: #666;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
            color: #18251c;
        }

        /* TABLE */

        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        .card h2 {
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #18251c;
            color: white;
        }

        tr:hover {
            background: #f8f7f2;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            background: #e7eee8;
        }

        @media (max-width: 900px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .stats {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 15px;
            }

        }

    </style>

</head>

<body>

    <div class="header">

        <h1>Groving Soil CRM</h1>

        <p>
            Visit Requests & Customer Management
        </p>

    </div>


    <div class="container">

        <!-- CRM STATISTICS -->

        <div class="stats">

            <div class="stat-card">

                <h3>Total Leads</h3>

                <div class="number">
                    <?php echo $totalLeads; ?>
                </div>

            </div>


            <div class="stat-card">

                <h3>New Leads</h3>

                <div class="number">
                    <?php echo $newLeads; ?>
                </div>

            </div>


            <div class="stat-card">

                <h3>Contacted</h3>

                <div class="number">
                    <?php echo $contactedLeads; ?>
                </div>

            </div>


            <div class="stat-card">

                <h3>Closed</h3>

                <div class="number">
                    <?php echo $closedLeads; ?>
                </div>

            </div>

        </div>


        <!-- VISIT REQUESTS -->

        <div class="card">

            <h2>Visit Requests</h2>
<form method="GET" class="search-form">

    <input
        type="text"
        name="search"
        placeholder="Search by name, phone or email..."
        value="<?php echo htmlspecialchars($_GET["search"] ?? ""); ?>"
    >

    <button type="submit">
        Search
    </button>

    <a href="dashboard.php" class="clear-search">
        Clear
    </a>

</form>

            <?php if ($result && $result->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Phone</th>

                            <th>Email</th>

                            <th>Visit Date</th>

                            <th>Message</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                
                                    <?php echo htmlspecialchars($row["id"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["phone"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["email"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["visit_date"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["message"]); ?>
                                </td>

                           <td>
                           

    <form action="update_status.php" method="POST">

        <input
            type="hidden"
            name="id"
            value="<?php echo $row["id"]; ?>"
        >

        <select
            name="status"
            onchange="this.form.submit()"
            class="status-select"
        >

            <option value="New"
                <?php echo $row["status"] === "New" ? "selected" : ""; ?>>
                New
            </option>

            <option value="Contacted"
                <?php echo $row["status"] === "Contacted" ? "selected" : ""; ?>>
                Contacted
            </option>

            <option value="Visited"
                <?php echo $row["status"] === "Visited" ? "selected" : ""; ?>>
                Visited
            </option>

            <option value="Closed"
                <?php echo $row["status"] === "Closed" ? "selected" : ""; ?>>
                Closed
            </option>

        </select>

    </form>

</td>
<td>

    <form
        action="delete_lead.php"
        method="POST"
        onsubmit="return confirm('Are you sure you want to delete this lead?');"
    >

        <input
            type="hidden"
            name="id"
            value="<?php echo $row["id"]; ?>"
        >

        <button type="submit" class="delete-btn">
            Delete
        </button>

    </form>

</td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <p>No visit requests found.</p>

            <?php endif; ?>


        </div>

    </div>


</body>

</html>

<?php

$conn->close();

?>