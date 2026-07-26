import React from 'react';
export function Toast({tone='success',children,style}){
  const bar={success:'var(--success)',error:'var(--red-600)',info:'var(--gray-600)'}[tone];
  return <div role="status" style={{display:'flex',alignItems:'center',gap:12,padding:'12px 16px',background:'var(--ink-900)',color:'var(--text-onstage)',
    borderRadius:'var(--radius-md)',boxShadow:'var(--shadow-raised)',fontFamily:'var(--font-body)',fontSize:14.5,maxWidth:420,...style}}>
    <span style={{width:4,alignSelf:'stretch',borderRadius:2,background:bar,flex:'0 0 auto'}}></span>
    <span>{children}</span>
  </div>;
}
export function Tooltip({label,children}){
  const [show,setShow]=React.useState(false);
  return <span style={{position:'relative',display:'inline-flex'}} onMouseEnter={()=>setShow(true)} onMouseLeave={()=>setShow(false)}>
    {children}
    {show&&<span role="tooltip" style={{position:'absolute',bottom:'calc(100% + 8px)',left:'50%',transform:'translateX(-50%)',whiteSpace:'nowrap',
      background:'var(--ink-900)',color:'var(--text-onstage)',fontFamily:'var(--font-body)',fontSize:13,padding:'6px 10px',borderRadius:'var(--radius-sm)',boxShadow:'var(--shadow-card)',zIndex:50}}>{label}</span>}
  </span>;
}
