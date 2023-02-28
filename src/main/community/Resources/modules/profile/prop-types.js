import {PropTypes as T} from 'prop-types'

const ProfileFacetSection = {
  propTypes: {
    id: T.string.isRequired,
    title: T.string.isRequired,
    display: T.shape({
      order: T.number
    }),
    fields: T.arrayOf(T.shape({

    }))
  }
}

const ProfileFacet = {
  propTypes: {
    id: T.string.isRequired,
    title: T.string.isRequired,
    meta: T.shape({
      main: T.bool
    }),
    display: T.shape({
      order: T.number,
      creation: T.bool
    }),
    sections: T.arrayOf(T.shape(
      ProfileFacetSection.propTypes
    ))
  },
  defaultProps: {
    title: '',
    meta: {
      main: false
    },
    display: {
      order: 0,
      creation: false
    },
    sections: []
  }
}

const Profile = {
  propTypes: {
    user: T.shape({

    }).isRequired,
    facets: T.arrayOf(T.shape(
      ProfileFacet.propTypes
    )),
    openFacet: T.func.isRequired
  }
}

export {
  Profile,
  ProfileFacet,
  ProfileFacetSection
}