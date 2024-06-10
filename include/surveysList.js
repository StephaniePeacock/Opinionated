// get query parameters
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
            addEventListenersToButtons(); // Add this line
        }
    };
    xhttp.open("GET", "include/surveysList.php", true);
    xhttp.send();
}

window.onload = function() {
    loadSurveys();
};

function addEventListenersToButtons() {
    const buttons = document.querySelectorAll("button[onclick^='takeSurvey']");
    buttons.forEach(button => {
        if (button.disabled) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                alert('You have already completed this survey. Please choose another.');
            });
        }
    });
}

function takeSurvey(id) {
    window.location.href = 'takeSurvey.html?id=' + id;
}
