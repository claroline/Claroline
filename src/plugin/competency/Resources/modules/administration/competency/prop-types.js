import {PropTypes as T} from 'prop-types'

const Level = {
  propTypes: {
    id: T.string,
    name: T.string,
    value: T.number
  }
}

const Scale = {
  propTypes: {
    id: T.string,
    name: T.string,
    levels: T.arrayOf(T.shape({
      id: T.string,
      value: T.string
    }))
  }
}

const Competency = {
  propTypes: {
    id: T.string,
    name: T.string,
    description: T.string,
    parent: T.object,
    scale: T.shape(Scale.propTypes),
    meta: T.shape({
      resourceCount: T.number
    }),
    structure: T.shape({
      root: T.number,
      lvl: T.number,
      lft: T.number,
      rgt: T.number
    }),
    abilities: T.arrayOf(T.object)
  }
}

const Ability = {
  propTypes: {
    id: T.string,
    name: T.string,
    minResourceCount: T.number,
    minEvaluatedResourceCount: T.number
  }
}

const CompetencyAbility = {
  propTypes: {
    id: T.string,
    competency: T.shape(Competency.propTypes),
    ability: T.shape(Ability.propTypes),
    level: T.shape(Level.propTypes)
  }
}

export {
  Level,
  Scale,
  Competency,
  Ability,
  CompetencyAbility
}