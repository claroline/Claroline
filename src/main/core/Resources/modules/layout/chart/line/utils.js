import {max, min, extent} from 'd3-array'
import {scaleLinear, scaleBand, scaleTime} from 'd3-scale'
import {
  AXIS_TYPE_X,
  AXIS_TYPE_Y,
  DATE_DATA_TYPE,
  NUMBER_DATA_TYPE,
  STRING_DATA_TYPE
} from '#/main/core/layout/chart/enums'

/**
 * Performs axis scaling for Line charts depending on dataType.
 *
 * @param values
 * @param type
 * @param dataType
 * @param size
 * @param minMaxAsDomain
 * @return {func} - the scale function
 */
const scaleAxis = (values, type, dataType = STRING_DATA_TYPE, size, minMaxAsDomain = false) => {
  let scale, minValue, maxValue
  switch (dataType) {
    case NUMBER_DATA_TYPE:
      minValue = minMaxAsDomain ? min(values) : 0
      maxValue = max(values)
      if (!minMaxAsDomain && maxValue >= 5) {
        const step = (maxValue - (maxValue % 5)) / 5
        maxValue = step*5 < maxValue ? step*6 : step*5
      }

      scale = scaleLinear().domain([minValue, maxValue])
      break

    case DATE_DATA_TYPE:
      scale = scaleTime().domain(extent(values))
      break

    case STRING_DATA_TYPE:
    default:
      scale = scaleBand()
        .domain(values)
        .paddingInner([0.2])
      break
  }

  switch (type) {
    case AXIS_TYPE_X:
      scale.range([0, size])
      break
    case AXIS_TYPE_Y:
      scale.range([size, 0])
  }

  return scale
}

export {
  scaleAxis
}
