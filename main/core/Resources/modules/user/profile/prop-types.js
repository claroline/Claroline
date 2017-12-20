import {PropTypes as T} from 'prop-types'

const ProfileFacetSection = {
  propTypes: {
    id: T.string.isRequired,
    title: T.string.isRequired,
    position: T.number,
    display: T.shape({
      collapsed: T.bool
    }),
    roles: T.arrayOf(T.shape({
      edit: T.bool,
      open: T.bool,
      role: T.shape({
        // todo get from role prop-types
      })
    })),
    fields: T.arrayOf(T.shape({

    }))
  }
}

const ProfileFacet = {
  propTypes: {
    id: T.string.isRequired,
    title: T.string.isRequired,
    position: T.number,
    display: T.shape({
      creation: T.bool
    }),
    meta: T.shape({
      main: T.bool
    }),
    sections: T.arrayOf(T.shape(
      ProfileFacetSection.propTypes
    ))
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