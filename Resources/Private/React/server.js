import React, { Fragment } from 'react';
import ReactDom from 'react-dom/server';
import { func } from 'prop-types';
import { FlowProvider, FlowClient } from '@bytorsten/react';
import { getDataFromTree } from '@bytorsten/react/server';
import components from '@fusion/components';
import cheerio from 'cheerio';

const { Provider, Consumer } = React.createContext();
export const App = ({ children }) => (
  <Consumer>
    {client => (
      <FlowProvider client={client}>
        <Fragment>
          {children({
            components: components.map(({ identifier, component: Component }) => (
              <div data-component={identifier} key={identifier}>
                <Component {...client.context.__props[identifier]}/>
              </div>
            ))
          })}
        </Fragment>
      </FlowProvider>
    )}
  </Consumer>
);

App.propTypes = {
  children: func
};

App.defaultProps = {
  children: ({ components }) => components
};

export const parseMarkup = markup => {
  const $ = cheerio.load(markup);
  const markups = {};
  $('[data-component]').each((_, element) => {
    markups[$(element).attr('data-component')] = $(element).html();
  });
  return markups;
};

export const createRenderer = component => async ({ context }) => {

  const client = new FlowClient({ context });
  const app = (
    <Provider value={client}>
      {component}
    </Provider>
  );

  await getDataFromTree(app);
  const content = ReactDom.renderToString(app);

  return { markups: parseMarkup(content), state: client.extract() };
};
