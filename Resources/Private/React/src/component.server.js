import React from 'react';
import { renderToString, renderToStaticMarkup } from 'react-dom/server';
import { FlowProvider, FlowClient } from '@bytorsten/react';
import { getDataFromTree } from '@bytorsten/react/server';

import Component from '@fusion/component';

export default async ({ context }) => {

  const client = new FlowClient({ context });

  const app = (
    <FlowProvider client={client}>
      <Component {...context} />
    </FlowProvider>
  );

  await getDataFromTree(app);
  const content = renderToString(app);
  return { content, state: client.extract() };
};
