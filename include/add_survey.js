let questions = [];
const customAnswerId = 46; // Predefined ID for the custom answer

document.addEventListener('DOMContentLoaded', function() {
    const survey_id = new URLSearchParams(window.location.search).get('id');
    if (survey_id) {
        fetch(`include/update_survey.php?id=${survey_id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('survey_id').value = data.survey_id;
                document.getElementById('title').value = data.title;
                document.getElementById('description').value = data.description;
                questions = data.questions.map(question => ({
                    id_question: question.id_question,
                    text: question.text,
                    answers: question.answers.map(answer => ({
                        id_answer: answer.id_answer,
                        text: answer.text,
                        is_custom: answer.is_custom,
                        response_count: answer.response_count || 0
                    })),
                    hasCustom: question.answers.some(answer => answer.is_custom)
                }));
                showQuestions();
            })
            .catch(error => console.error('Error:', error));
    }
});

function showQuestionInput() {
    document.getElementById('question_text').value = '';
    document.getElementById('question_input_container').classList.remove('hidden');
}

function addQuestion() {
    const questionText = document.getElementById('question_text').value;
    if (!questionText) {
        displayError('Please enter a question text');
        return;
    }
    const question = { text: questionText, answers: [], hasCustom: false };
    questions.push(question);

    const questionsContainer = document.getElementById('questions_container');
    const questionDiv = document.createElement('div');
    questionDiv.className = 'question';
    questionDiv.innerHTML = `
        <h3>${questionText}</h3>
        <button type="button" class="remove-question" onclick="removeQuestion(${questions.length - 1})">Remove Question</button>
        <div class="answers" id="answers_container_${questions.length - 1}"></div>
        <div class="add-answer-container">
            <input type="checkbox" id="is_custom_${questions.length - 1}" onchange="toggleCustom(${questions.length - 1})"> Custom Answer
            <input type="text" placeholder="Answer Text" id="answer_text_${questions.length - 1}">
            <button type="button" onclick="addAnswer(${questions.length - 1})">Add Answer</button>
        </div>
    `;
    questionsContainer.appendChild(questionDiv);

    document.getElementById('question_input_container').classList.add('hidden');
}

function toggleCustom(indx) {
    const isCustom = document.getElementById(`is_custom_${indx}`).checked;
    const answerTextInput = document.getElementById(`answer_text_${indx}`);

    if (isCustom) {
        const question = questions[indx];
        if (question.hasCustom) {
            displayError('Only one custom answer is allowed per question.');
            document.getElementById(`is_custom_${indx}`).checked = false;
            return;
        }
        answerTextInput.classList.add('hidden');
    } else {
        answerTextInput.classList.remove('hidden');
    }
}

function addAnswer(indx) {
    const isCustom = document.getElementById(`is_custom_${indx}`).checked;
    const answerText = document.getElementById(`answer_text_${indx}`).value;

    const question = questions[indx];
    if (isCustom) {
        if (question.hasCustom) {
            displayError('Only one custom answer is allowed per question.');
            return;
        }
        question.hasCustom = true;
        question.answers.push({
            id_answer: customAnswerId,
            text: 'Other',
            is_custom: true,
            response_count: 0
        });
    } else {
        if (!answerText) {
            displayError('Please enter an answer text');
            return;
        }
        question.answers.push({
            text: answerText,
            is_custom: false,
            response_count: 0
        });
    }

    showAnswers(indx);

    document.getElementById(`answer_text_${indx}`).value = '';
    document.getElementById(`is_custom_${indx}`).checked = false;
    document.getElementById(`answer_text_${indx}`).classList.remove('hidden');
}

function removeAnswer(indx, answerIndex) {
    const question = questions[indx];
    if (question.answers[answerIndex].is_custom) {
        question.hasCustom = false;
    }
    question.answers.splice(answerIndex, 1);
    showAnswers(indx);
}

function showAnswers(indx) {
    const answersContainer = document.getElementById(`answers_container_${indx}`);
    answersContainer.innerHTML = '';
    questions[indx].answers.forEach((answer, index) => {
        const answerDiv = document.createElement('div');
        answerDiv.className = 'answer';
        answerDiv.innerHTML = `
            ${answer.text}
            <button type="button" onclick="removeAnswer(${indx}, ${index})">Remove</button>
        `;
        answersContainer.appendChild(answerDiv);
    });
}

function removeQuestion(indx) {
    questions.splice(indx, 1);
    showQuestions();
}

function showQuestions() {
    const questionsContainer = document.getElementById('questions_container');
    questionsContainer.innerHTML = '';
    questions.forEach((question, index) => {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question';
        questionDiv.innerHTML = `
            <h3 contenteditable="true">${question.text}</h3>
            <button type="button" class="remove-question" onclick="removeQuestion(${index})">Remove Question</button>
            <div class="answers" id="answers_container_${index}"></div>
            <div class="add-answer-container">
                <input type="checkbox" id="is_custom_${index}" onchange="toggleCustom(${index})"> Custom Answer
                <input type="text" placeholder="Answer Text" id="answer_text_${index}">
                <button type="button" onclick="addAnswer(${index})">Add Answer</button>
            </div>
        `;
        questionsContainer.appendChild(questionDiv);
        showAnswers(index);
    });
}

function submitSurvey() {
    const survey_id = document.getElementById('survey_id').value;
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;

    document.getElementById('error').innerText = '';

    if (!title) {
        displayError('Please enter a survey title.');
        return;
    }

    if (!description) {
        displayError('Please enter a survey description.');
        return;
    }

    if (questions.length === 0) {
        displayError('Please add at least one question.');
        return;
    }

    for (const question of questions) {
        if (question.answers.length === 0) {
            displayError('Each question must have at least one answer.');
            return;
        }
    }

    const formData = new FormData();
    formData.append('survey_id', survey_id);
    formData.append('title', title);
    formData.append('description', description);
    formData.append('questions', JSON.stringify(questions));

    fetch('include/add_survey.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = 'surveyMgmt.html';
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
    document.getElementById('error').innerText = message;
}
