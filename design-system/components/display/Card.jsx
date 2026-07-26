import React from 'react';
export function Card({variant='default',pad=true,children,style,...rest}){
  const v={
    default:{background:'var(--surface-card)',border:'1px solid var(--border-subtle)',boxShadow:'var(--shadow-card)',color:'var(--text-body)'},
    stage:{background:'var(--surface-stage-raised)',border:'1px solid var(--border-onstage)',boxShadow:'var(--shadow-stage)',color:'var(--text-onstage)'},
    sunken:{background:'var(--surface-sunken)',border:'1px solid var(--border-subtle)',color:'var(--text-body)'},
  }[variant];
  return <div style={{borderRadius:'var(--radius-lg)',padding:pad?'var(--sp-6)':0,overflow:'hidden',fontFamily:'var(--font-body)',...v,...style}} {...rest}>{children}</div>;
}
