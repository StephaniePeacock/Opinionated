document.addEventListener('DOMContentLoaded', function() {
    const survey_id = new URLSearchParams(window.location.search).get('id');
    fetch(`include/results.php?id=${survey_id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                displayError(data.message);
            } else {
                document.getElementById('surveyTitle').textContent = data.survey.title;
                document.getElementById('surveyDescription').textContent = data.survey.description;
                showQuestions(data.questions);
            }
        })
        .catch(error => console.error('Error:', error));
});

function showQuestions(questions) {
    const questionsContainer = document.getElementById('questionsContainer');
    questionsContainer.innerHTML = '';
    questions.forEach(question => {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question';

        const questionTitle = document.createElement('h2');
        questionTitle.textContent = question.text;
        questionDiv.appendChild(questionTitle);

        const predefinedContainer = document.createElement('div');
        predefinedContainer.className = 'chart-container';

        let totalResponses = 0;
        question.predefinedAnswers.forEach(answer => {
            totalResponses += answer.count;
        });
        question.customAnswers.forEach(answer => {
            totalResponses += answer.count;
        });

        // Filter out the 'Other' answer from predefined answers
        const filteredPredefinedAnswers = question.predefinedAnswers.filter(answer => answer.text !== 'Other');

        filteredPredefinedAnswers.forEach(answer => {
            const answerDiv = createBar(answer, totalResponses);
            predefinedContainer.appendChild(answerDiv);
        });

        if (question.customAnswers.length > 0) {
            const otherAnswer = { text: 'Other', count: 0, percentage: 0 };
            question.customAnswers.forEach(answer => {
                otherAnswer.count += answer.count;
            });

            otherAnswer.percentage = ((otherAnswer.count / totalResponses) * 100).toFixed(0);
            const otherDiv = createBar(otherAnswer, totalResponses);
            predefinedContainer.appendChild(otherDiv);
        }

        questionDiv.appendChild(predefinedContainer);

        if (question.customAnswers.length > 0) {
            const customContainer = document.createElement('div');
            customContainer.className = 'custom-answers';

            const customTitle = document.createElement('h4');
            customTitle.textContent = 'Custom Answers';
            customContainer.appendChild(customTitle);

            question.customAnswers.forEach(answer => {
                const customAnswerDiv = document.createElement('div');
                customAnswerDiv.className = 'custom-answer';
                const answerText = document.createElement('span');
                answerText.textContent = answer.text;
                const answerCount = document.createElement('span');
                answerCount.textContent = answer.count;
                const answerPercentage = document.createElement('span');
                const percentage = ((answer.count / totalResponses) * 100).toFixed(0);
                answerPercentage.textContent = `${percentage}%`;

                customAnswerDiv.appendChild(answerText);
                customAnswerDiv.appendChild(answerCount);
                customAnswerDiv.appendChild(answerPercentage);
                customContainer.appendChild(customAnswerDiv);
            });

            questionDiv.appendChild(customContainer);
        }

        questionsContainer.appendChild(questionDiv);
    });
}


function createBar(answer, totalResponses) {
    const percentage = ((answer.count / totalResponses) * 100).toFixed(0);

    const answerBox = document.createElement('div');
    answerBox.className = 'answer-box';

    const bar = document.createElement('div');
    bar.className = 'bar';

    const barInner = document.createElement('div');
    barInner.className = 'bar-inner';
    barInner.style.height = percentage + '%';

    const percentageLabel = document.createElement('div');
    percentageLabel.className = 'percentage-label';
    percentageLabel.textContent = percentage + '%';
    barInner.appendChild(percentageLabel);

    const answerLabel = document.createElement('div');
    answerLabel.className = 'answer-label';
    answerLabel.textContent = answer.text;

    const countLabel = document.createElement('div');
    countLabel.className = 'count-label';
    countLabel.textContent = answer.count;

    bar.appendChild(barInner);
    answerBox.appendChild(bar);
    answerBox.appendChild(answerLabel);
    answerBox.appendChild(countLabel);

    return answerBox;
}

function displayError(message) {
    const errorContainer = document.getElementById('error');
    if (errorContainer) {
        errorContainer.innerText = message;
    } else {
        alert(message);
    }
}
