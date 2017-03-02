export const utils = {}

/**
 * @var id solution id
 * @var set first or second set
 *
 */
utils.getSolutionData = (id, set) => {
  return set.find(item => item.id === id).data
}
