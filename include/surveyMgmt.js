// Function to get query parameters
function getQueryParam(param) {
    let params = new URLSearchParams(window.location.search);
    return params.get(param);
}

// Display error message if exists
let error = getQueryParam('error');
if (error) {
    alert(decodeURIComponent(error));
}

function loadSurveys() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("surveyTable").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "include/get_surveys.php", true);
    xhttp.send();
}

window.onload = function() {
    loadSurveys();
};

function modifySurvey(id) {
    window.location.href = 'modify_survey.html?id=' + id;
}

function deleteSurvey(id) {
    if (confirm('Are you sure you want to delete this survey?')) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "include/delete_survey.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                alert('Survey deleted successfully');
                loadSurveys();
            }
        };
        xhttp.send("id=" + id);
    }
}
