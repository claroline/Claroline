
export default class PaperGenerator {
  /**
   * Constructor.
   *
   * @param {object} $filter
   * @param {IdentifierService} IdentifierService
   * @param {ArrayService} ArrayService
   */
  constructor($filter, IdentifierService, ArrayService) {
    this.$filter = $filter
    this.IdentifierService = IdentifierService
    this.ArrayService = ArrayService
  }

  generate(exercise, user, previousPaper) {
    return {
      id: this.IdentifierService.generateUUID(),
      number: previousPaper ? previousPaper.number + 1 : 1,
      interrupted: true,
      user: user,
      start: this.$filter('date')(new Date(), 'yyyy-MM-dd\'T\'HH:mm:ss'),
      end: null,
      order: previousPaper && exercise.meta.keepSteps ? previousPaper.order : this.generateOrder(exercise),
      questions: []
    }
  }

  generateOrder(exercise) {
    let steps = []

    for (let i = 0; i < exercise.steps.length; i++) {
      let step = exercise.steps[i]

      // Pick items in step
      let items = []
      for (let j = 0; j < step.items.length; j++) {
        items.push(step.items[j].id)
      }

      steps.push({
        id: step.id,
        items: items
      })
    }

    if (exercise.meta.random) {
      this.ArrayService.shuffle(steps)
    }

    if (exercise.meta.pick > 0) {
      return this.ArrayService.pick(steps, exercise.meta.pick)
    } else {
      return steps
    }
  }
}
