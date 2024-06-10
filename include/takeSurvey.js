let questions = [];
const customAnswerId = 46; // Predefined ID for the custom answer

document.addEventListener('DOMContentLoaded', function() {
    const survey_id = new URLSearchParams(window.location.search).get('id');
    fetch(`include/takeSurvey.php?id=${survey_id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                displayError(data.message);
            } else {
                document.getElementById('survey_id').value = data.survey_id;
                document.getElementById('title').textContent = data.title;
                document.getElementById('description').textContent = data.description;
                questions = data.questions;
                showQuestions();
            }
        })
        .catch(error => console.error('Error:', error));
});

function showQuestions() {
    const questionsContainer = document.getElementById('questions_container');
    questionsContainer.innerHTML = '';
    questions.forEach((question, index) => {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question';
        questionDiv.innerHTML = `
            <h3>${question.text}</h3>
            <div class="answers" id="answers_container_${index}"></div>`;
        questionsContainer.appendChild(questionDiv);
        showAnswers(index);
    });
}

function showAnswers(indx) {
    const answersContainer = document.getElementById(`answers_container_${indx}`);
    answersContainer.innerHTML = '';
    let otherAnswer = null;
    questions[indx].answers.forEach((answer, index) => {
        if (answer.id_answer === customAnswerId) {
            otherAnswer = answer;
            return;
        }
        const answerDiv = document.createElement('div');
        answerDiv.className = 'answer';
        answerDiv.style.display = 'flex';
        answerDiv.style.justifyContent = 'flex-start';
        answerDiv.innerHTML = `
            <input type="radio" name="question_${indx}" value="${answer.id_answer}" style="margin-right: 10px;"> ${answer.text}`;
        answersContainer.appendChild(answerDiv);
    });

    if (otherAnswer) {
        const answerDiv = document.createElement('div');
        answerDiv.className = 'answer';
        answerDiv.style.display = 'flex';
        answerDiv.style.justifyContent = 'flex-start';
        answerDiv.innerHTML = `
            <input type="radio" name="question_${indx}" value="${otherAnswer.id_answer}" style="margin-right: 10px;">
            <input type="text" placeholder="Other" id="custom_answer_${indx}">
        `;
        answersContainer.appendChild(answerDiv);
    }

    // Make all answer divs the same height
    const answerDivs = answersContainer.querySelectorAll('.answer');
    const maxHeight = Math.max(...Array.from(answerDivs).map(div => div.offsetHeight));
    answerDivs.forEach(div => div.style.height = `${maxHeight}px`);
}

function submitSurvey() {
    const survey_id = document.getElementById('survey_id').value;
    const responses = [];

    for (let i = 0; i < questions.length; i++) {
        const selectedAnswer = document.querySelector(`input[name="question_${i}"]:checked`);
        if (selectedAnswer) {
            const answerId = selectedAnswer.value;
            const customText = (answerId == customAnswerId) ? document.getElementById(`custom_answer_${i}`).value : null;
            responses.push({
                question_id: questions[i].id_question,
                answer_id: answerId,
                custom_text: customText
            });
        } else {
            displayError('Please answer all questions.');
            return;
        }
    }

    const formData = new FormData();
    formData.append('survey_id', survey_id);
    formData.append('responses', JSON.stringify(responses));

    fetch('include/takeSurvey.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text()) // Read response as text first
    .then(text => {
        console.log(text); // Log the raw response for debugging
        return JSON.parse(text); // Then parse JSON from the raw response
    })
    .then(data => {
        console.log(data); // Add this line to debug
        if (data.success) {
            alert(data.message);
            window.location.href = 'surveysList.html';
        } else {
            displayError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        displayError('An unexpected error occurred.');
    });
}

function displayError(message) {
    const errorContainer = document.getElementById('error');
    if (errorContainer) {
        errorContainer.innerText = message;
    } else {
        alert(message);
    }
}
