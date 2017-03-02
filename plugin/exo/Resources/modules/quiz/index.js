import {Quiz} from './quiz'

const container = document.querySelector('.quiz-container')
const rawQuizData = JSON.parse(container.dataset.quiz)
const noServer = !!container.dataset.noServer
const quiz = new Quiz(rawQuizData, noServer)

quiz.render(container)
