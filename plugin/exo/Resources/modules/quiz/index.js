import {Quiz} from './quiz'

const container = document.querySelector('.quiz-container')
const rawQuizData = JSON.parse(container.dataset.quiz)
const rawResourceNodeData = JSON.parse(container.dataset.resourceNode)
const noServer = !!container.dataset.noServer
const quiz = new Quiz(rawQuizData, rawResourceNodeData, noServer)

quiz.render(container)
