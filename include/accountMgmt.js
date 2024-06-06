// Function to get query parameters
function getQueryParam(param) {
    let params = new URLSearchParams(window.location.search);
    return params.get(param);
}

// Display error message if exists
let error = getQueryParam('error');
if (error) {
    document.getElementById('error').innerText = decodeURIComponent(error);
}

// Function to get cookie value by name
function getCookie(name) {
    let cookieArr = document.cookie.split(";");
    for (let i = 0; i < cookieArr.length; i++) {
        let cookiePair = cookieArr[i].split("=");
        if (name === cookiePair[0].trim()) {
            return decodeURIComponent(cookiePair[1]);
        }
    }
    return null;
}

function loadUser() {
    let userDataCookie = getCookie('user_data');
    if (userDataCookie) {
        let userData = JSON.parse(userDataCookie);
        let userTable = document.getElementById('userTable');
        userTable.innerHTML = '';

        let fields = {
            "Email": userData.EMAIL,
            "Password": '',
            "Surveys Answered": userData.SURVEYS,
            "Current Rank": userData.RANK_TITLE
        };

        for (let key in fields) {
            let row = document.createElement('tr');

            let headerCell = document.createElement('th');
            headerCell.innerText = key;
            row.appendChild(headerCell);

            let dataCell = document.createElement('td');
            dataCell.className = key.toLowerCase().replace(/ /g, "_");
            dataCell.innerText = fields[key];
            if (key === "Password" || key === "Email") {
                dataCell.contentEditable = true; // Make Email and Password fields editable
            }
            row.appendChild(dataCell);

            userTable.appendChild(row);
        }

        document.getElementById('updateButton').onclick = function() { updateUser(userData.USER_UID); };
        document.getElementById('deleteButton').onclick = function() { deleteUser(userData.USER_UID); };
    } else {
        document.getElementById('error').innerText = "User data not found. Please log in again.";
    }
}

window.onload = function() {
    loadUser();
};

function updateUser(id) {
    var email = document.querySelector('.email').innerText;
    var password = document.querySelector('.password').innerText;
    var surveys = document.querySelector('.surveys_answered').innerText;
    var rank = document.querySelector('.current_rank').innerText;
    
    // Retrieve original cookie data
    let userDataCookie = getCookie('user_data');
    let is_admin = 0; // Default value if not found
    if (userDataCookie) {
        let userData = JSON.parse(userDataCookie);
        is_admin = userData.IS_ADMIN || 0; // Use parsed value if available
    }

    var xhttp = new XMLHttpRequest();
    xhttp.open("POST", "include/update_user.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                var response = JSON.parse(this.responseText);
                if (response.error) {
                    alert('Error updating user: ' + response.error);
                } else if (response.success) {
                    // Update relevant cookie entries
                    let updatedUserData = {
                        EMAIL: email,
                        PASSWORD: password,
                        SURVEYS: surveys,
                        RANK_TITLE: rank,
                        IS_ADMIN: is_admin
                    };
                    document.cookie = 'user_data=' + JSON.stringify(updatedUserData) + '; path=/'; // Rewrite the cookie

                    alert('User updated successfully');
                    loadUser();
                    window.location.reload(); // Reload the page to display the new data
                }
                
            } else {
                alert('Error updating user. Status: ' + this.status);
            }
        }
    };

    xhttp.send("id=" + id + "&email=" + encodeURIComponent(email) + 
               "&password=" + encodeURIComponent(password) + 
               "&surveys=" + encodeURIComponent(surveys) + 
               "&rank=" + encodeURIComponent(rank) +
               "&is_admin=" + encodeURIComponent(is_admin));
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "include/delete_user.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                alert('User deleted successfully');
                window.location.href = 'login.html';
            }
        };
        xhttp.send("id=" + id);
    }
}
