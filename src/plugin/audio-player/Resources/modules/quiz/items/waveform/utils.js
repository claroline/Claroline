function isOverlayed(sections, start, end, excludedIndex) {
  let overlayed = false

  sections.forEach((s, idx) => {
    const startTolerance = s.startTolerance ? s.startTolerance : 0
    const endTolerance = s.endTolerance ? s.endTolerance : 0

    if (idx !== excludedIndex && (
      (start >= s.start - startTolerance && start <= s.end + endTolerance) ||
      (end >= s.start - startTolerance && end <= s.end + endTolerance) ||
      (start <= s.start - startTolerance && end >= s.end + endTolerance)
    )) {
      overlayed = true
    }
  })

  return overlayed
}

function isCorrectAnswer(solutions, start, end) {
  let isCorrect = false

  solutions.forEach(solution => {
    if (0 < solution.score &&
      start >= solution.section.start - solution.section.startTolerance &&
      start <= solution.section.start + solution.section.startTolerance &&
      end >= solution.section.end - solution.section.endTolerance &&
      end <= solution.section.end + solution.section.endTolerance
    ) {
      isCorrect = true
    }
  })

  return isCorrect
}

export {
  isOverlayed,
  isCorrectAnswer
}
