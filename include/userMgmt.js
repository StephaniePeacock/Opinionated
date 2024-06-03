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

function loadUsers() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("userTable").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "include/get_users.php", true);
    xhttp.send();
}

window.onload = function() {
    loadUsers();
};

function updateUser(id) {
    var row = document.getElementById('user-' + id);
    var email = row.querySelector('.email').innerText;
    var password = row.querySelector('.password').innerText;
    var isAdmin = row.querySelector('.is_admin').innerText;
    var rank = row.querySelector('.rank').innerText;
    var surveys = row.querySelector('.surveys').innerText;

    var xhttp = new XMLHttpRequest();
    xhttp.open("POST", "include/update_user.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                var response = JSON.parse(this.responseText);
                if (response.error) {
                    alert('Error updating user: ' + response.error);
                    loadUsers();
                } else if (response.success) {
                    alert('User updated successfully');
                    loadUsers();
                }
            } else {
                alert('Error updating user. Status: ' + this.status);
            }
        }
    };
    xhttp.send("id=" + id + "&email=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(password) + 
        "&is_admin=" + encodeURIComponent(isAdmin) + "&rank=" + encodeURIComponent(rank) + "&surveys=" + encodeURIComponent(surveys));
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "include/delete_user.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                alert('User deleted successfully');
                loadUsers();
            }
        };
        xhttp.send("id=" + id);
    }
}
