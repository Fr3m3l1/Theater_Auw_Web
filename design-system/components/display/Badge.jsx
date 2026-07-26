import React from 'react';
export function Badge({tone='red',children,style}){
  const t={
    red:{background:'var(--red-600)',color:'#fff'},
    gold:{background:'var(--gold-500)',color:'var(--ink-950)'},
    outline:{background:'transparent',color:'var(--text-body)',border:'1px solid var(--border-strong)'},
    success:{background:'var(--success-bg)',color:'var(--success)'},
    warning:{background:'var(--warning-bg)',color:'var(--warning)'},
    stage:{background:'rgba(248,246,242,.12)',color:'var(--text-onstage)',border:'1px solid var(--border-onstage)'},
  }[tone];
  return <span style={{display:'inline-flex',alignItems:'center',gap:6,padding:'4px 12px',borderRadius:'var(--radius-pill)',
    fontFamily:'var(--font-display)',fontWeight:600,fontSize:14,letterSpacing:'var(--tracking-caps)',textTransform:'uppercase',lineHeight:1.3,...t,...style}}>{children}</span>;
}
export function Tag({children,style}){
  return <span style={{display:'inline-flex',alignItems:'center',padding:'3px 10px',borderRadius:'var(--radius-sm)',background:'var(--surface-sunken)',
    color:'var(--gray-600)',fontFamily:'var(--font-body)',fontSize:13,fontWeight:500,...style}}>{children}</span>;
}
