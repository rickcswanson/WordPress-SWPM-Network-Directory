<!-- global-network-level-directory-template.php -->

<style>
    .member-list {
        border-top: 1px solid #000;
        padding: 10px;
        margin-top: 25px;
        margin-bottom: 50px;
    }

    .member {
        border-bottom: 1px solid #000;
        padding: 10px 0;
    }

    .label {
        font-weight: bold;
    }

    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }

    .search-container {
        margin-bottom: 20px;
    }

    .search-container input[type=text] {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 300px;
        margin-right: 10px;
    }

    .search-container select {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        cursor: pointer;
    }

    .search-container input[type=submit] {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

/* Style for download button */
#download-btn {
    padding: 15px 30px;
    background-color: #000000;
    color: white;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    margin-bottom: 40px;
    display: block;
    margin: 0 auto;
    transition: background-color 0.2s ease, box-shadow 0.3s ease;
}

#download-btn:hover {
    background-color: #E40521;
    box-shadow: 0 0 15px rgba(228, 5, 33, 0.3);
}
</style>

<!-- HTML begins here -->
<div class="search-container">
    <form id="member-search-form" action="" method="get">
        <input type="text" placeholder="Search members..." name="search" id="search-input">
        <select id="account-status"> // Filters members by their account status
            <option value="all">All</option>
            <option value="pending">Pending</option>
            <option value="active">Active</option>
        </select>
        <select id="filter 2"> // this was used to filter members by their employer status
            <option value="all">All</option>
            <option value="value 1">value 1</option>
            <option value="value 2">Value 2</option>
            <option value="value 3">Value 3</option>
        </select>
    </form>
</div>

<!-- Download button -->
<button id="download-btn">Download CSV Report</button>

<div class="member-list" id="member-list">
    <?php $count = 0; ?>
    <?php foreach ($all_members as $member) : ?>
        <div class="member">
            <span class="label">Name: </span><span class="name"><?php echo empty($member->first_name) ? '------' : $member->first_name; ?> <?php echo empty($member->last_name) ? '------' : $member->last_name; ?></span><br>
            <span class="label">Email: </span><span class="email"><?php echo empty($member->email) ? '------' : $member->email; ?></span><br>
            <span class="label">Phone Type: </span><span class="phone-type"><?php echo isset($member->phone_type) ? $member->phone_type : '------'; ?></span><br>
            <span class="label">Phone: </span><span class="phone"><?php echo empty($member->phone) ? '------' : $member->phone; ?></span><br>
            <span class="label">Account Status: </span><span class="account_state"><?php echo empty($member->account_state) ? '------' : $member->account_state; ?></span><br>
            <span class="label">Membership Level: </span><span class="membership-level"><?php echo empty($member->membership_level) ? '------' : get_all_membership_level_names($member->membership_level); ?></span><br>
            <span class="label">Employee Status: </span><span class="employee-status"><?php echo isset($member->employee_status) ? $member->employee_status : '------'; ?></span><br>
            <span class="label">Badge Number: </span><span class="badge-number"><?php echo isset($member->badge_number) ? $member->badge_number : '------'; ?></span><br>
            <span class="label">Address: </span><span class="address"><?php echo (empty($member->address_street) && empty($member->address_city) && empty($member->address_state) && empty($member->address_zipcode)) ? '------' : $member->address_street . ', ' . $member->address_city . ', ' . $member->address_state . ' ' . $member->address_zipcode; ?></span><br>

            <!-- Add more fields as needed -->
        </div>
        <?php $count++; ?>
        <?php if ($count % 10 == 0) : ?>
            <div class="clearfix"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>


<!-- JavaScript begins here -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const accountStatusSelect = document.getElementById('account-status');
    const employeeStatusSelect = document.getElementById('employee-status');
    const memberList = document.getElementById('member-list');
    const memberItems = memberList.getElementsByClassName('member');

    // Function to filter members
    function filterAllMembers() {
        const searchValue = searchInput.value.trim().toLowerCase();
        const selectedAccountStatus = accountStatusSelect.value.trim().toLowerCase();
        const selectedEmployeeStatus = employeeStatusSelect.value.trim().toLowerCase();

        Array.from(memberItems).forEach(memberItem => {
            const memberData = memberItem.textContent.trim().toLowerCase();
            const accountStatusElement = memberItem.querySelector('.account_state');
            const employeeStatusElement = memberItem.querySelector('.employee-status');
            const accountStatus = accountStatusElement.textContent.trim().toLowerCase();
            const employeeStatus = employeeStatusElement.textContent.trim().toLowerCase();

            if ((searchValue === '' || memberData.includes(searchValue)) &&
                (selectedAccountStatus === 'all' || accountStatus === selectedAccountStatus) &&
                (selectedEmployeeStatus === 'all' || employeeStatus === selectedEmployeeStatus)) {
                memberItem.style.display = 'block'; // Show matching member
            } else {
                memberItem.style.display = 'none'; // Hide non-matching member
            }
        });
    }

    // Event listener for search input
    searchInput.addEventListener('input', filterAllMembers);

    // Event listener for account status select
    accountStatusSelect.addEventListener('change', filterAllMembers);

    // Event listener for employee status select
    employeeStatusSelect.addEventListener('change', filterAllMembers);

    // Function to convert array of objects to CSV format
    function convertAllToCSV(objArray) {
        const array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
        let str = '';

        // Headers
        const headers = Object.keys(array[0]);
        str += headers.join(',') + '\r\n';

        // Rows
        for (let i = 0; i < array.length; i++) {
            let line = '';
            for (let j = 0; j < headers.length; j++) {
                if (j > 0) line += ',';

                // Check if the current header exists in the current data object
                if (array[i].hasOwnProperty(headers[j])) {
                    // Concatenate address fields into one string
                    if (headers[j] === 'Address') {
                        line += '"' + [array[i]['Street'], array[i]['City'], array[i]['State'], array[i]['Zipcode']].join(', ') + '"';
                    } else {
                        line += array[i][headers[j]];
                    }
                } else {
                    line += ''; // Insert empty string if no value exists for the header
                }
            }
            str += line + '\r\n';
        }

        return str;
    }

    // Function to download CSV
    function downloadAllCSV(data, filename) {
        const csv = convertAllToCSV(data);
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });

        // Create a temporary link element
        const link = document.createElement("a");
        link.setAttribute("href", URL.createObjectURL(blob));
        link.setAttribute("download", filename);

        // Simulate click on the link
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Add event listener to download button
    document.getElementById('download-btn').addEventListener('click', function () {
        // Get currently displayed member data
        const displayedMemberData = Array.from(memberList.querySelectorAll('.member'))
            .filter(member => member.style.display !== 'none')
            .map(member => {
                const data = {};
                const labels = member.querySelectorAll('.label');
                const values = member.querySelectorAll('.name, .email, .phone, .account_state, .address, .membership-level, .phone-type, .employee-status, .badge-number');

                // Check if labels and values arrays have the same length
                if (labels.length === values.length) {
                    labels.forEach((label, index) => {
                        data[label.textContent.trim()] = values[index].textContent.trim();
                    });
                } else {
                    console.error('Labels and values array have different lengths');
                }

                return data;
            });

        // Debug statement to check displayedMemberData
        console.log(displayedMemberData);

        // Download CSV
        downloadAllCSV(displayedMemberData, 'member_report.csv');
    });
});

</script>
