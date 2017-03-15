export const utils = {
  getAnswerClass(solution, answer){
    if (answer === ''){
      return null
    } else if (solution.score > 0) {
      return answer === solution.id ? 'correct-answer' : null
    } else if (solution.score < 1) {
      return answer === solution.id ? 'incorrect-answer' : null
    }
  }
}
