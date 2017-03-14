export const utils = {
  getAnswerClass(solution, answer){
    if(answer === ''){
      return 'bg-choice'
    } else if (solution.score > 0) {
      return answer === solution.id ? 'text-success bg-success' : 'bg-choice'
    } else if (solution.score < 1) {
      return answer === solution.id ? 'text-danger bg-danger' : 'bg-choice'
    }
  }
}
