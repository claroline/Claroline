// transform raw papers data
export function normalize(rawPapers) {
  let papers = {}
  Object.keys(rawPapers).forEach(key => {
    const paper = rawPapers[key]
    papers[paper.id] = paper
  })
  return papers
}
