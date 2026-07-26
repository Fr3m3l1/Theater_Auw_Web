import React,{useState} from 'react';
export function Button({variant='primary',size='md',disabled=false,full=false,children,style,...rest}){
  const [hov,setHov]=useState(false),[act,setAct]=useState(false);
  const pads={sm:'7px 16px',md:'11px 22px',lg:'15px 30px'},fss={sm:14,md:15,lg:16};
  const v={
    primary:{background:act?'var(--accent-press)':hov?'var(--accent-hover)':'var(--accent)',color:'var(--text-onaccent)',border:'1px solid transparent'},
    secondary:{background:hov?'var(--surface-sunken)':'transparent',color:'var(--text-body)',border:'1px solid var(--border-strong)'},
    ghost:{background:hov?'var(--surface-sunken)':'transparent',color:'var(--accent)',border:'1px solid transparent'},
    stage:{background:hov?'#fff':'var(--text-onstage)',color:'var(--ink-950)',border:'1px solid transparent'},
  }[variant];
  return <button disabled={disabled} onMouseEnter={()=>setHov(true)} onMouseLeave={()=>{setHov(false);setAct(false)}} onMouseDown={()=>setAct(true)} onMouseUp={()=>setAct(false)}
    style={{display:full?'flex':'inline-flex',width:full?'100%':undefined,alignItems:'center',justifyContent:'center',gap:8,padding:pads[size],fontSize:fss[size],whiteSpace:'nowrap',flex:'0 0 auto',
    fontFamily:'var(--font-body)',fontWeight:600,borderRadius:'var(--radius-sm)',cursor:disabled?'not-allowed':'pointer',opacity:disabled?.45:1,
    transform:act&&!disabled?'translateY(1px)':'none',transition:'background var(--dur-fast) var(--ease-out),transform var(--dur-fast)',...v,...style}} {...rest}>{children}</button>;
}
