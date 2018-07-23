import React, { Fragment } from 'react';
import { string, bool } from 'prop-types';
import { Uri } from '@bytorsten/react';
import { FlowProvider, FlowClient, Context } from '@bytorsten/react';
import { renderToStaticMarkup } from 'react-dom/server';
import { getDataFromTree } from '@bytorsten/react/server';

const InternalData = ({ internalDataKey }) => {
  const { identifier, clientChunkName: chunkname, ...rest } = __internalData;

  return (
    <Fragment>
      <Uri forceFetch action="index" controller="rpc" package="bytorsten.react">
        {({ data }) => (
          <script dangerouslySetInnerHTML={{__html: `
            window.${internalDataKey} = ${JSON.stringify({
              endpoints: {
                rpc: data
              },
              ...rest
            })};
          `}} />
        )}
      </Uri>
      <Context>
        {({ externalPackages }) => externalPackages && (
          <Fragment>
            <Uri forceFetch action="index" controller="chunk" package="bytorsten.react" arguments={{ identifier, chunkname }}>
              {({ data }) => (
                <script defer type="module" src={data} />
              )}
            </Uri>
            <Uri forceFetch action="legacy" controller="chunk" package="bytorsten.react" arguments={{ identifier, chunkname }}>
              {({ data }) => (
                <script defer noModule src={data} />
              )}
            </Uri>
          </Fragment>
        )}
      </Context>
    </Fragment>
  );
};

InternalData.propTypes = {
  internalDataKey: string
};

InternalData.defaultProps = {
  internalDataKey: '__FLOW_HELPER__'
};

export default async ({ context }) => {
  const client = new FlowClient({ context });
  const app = (
    <FlowProvider client={client}>
      <InternalData />
    </FlowProvider>
  );

  await getDataFromTree(app);
  return renderToStaticMarkup(app);
};
