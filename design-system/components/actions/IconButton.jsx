import React,{useState} from 'react';
export function IconButton({label,size='md',variant='secondary',disabled=false,children,style,...rest}){
  const [hov,setHov]=useState(false);
  const dim={sm:30,md:38,lg:46}[size];
  const v={
    primary:{background:hov?'var(--accent-hover)':'var(--accent)',color:'#fff',border:'1px solid transparent'},
    secondary:{background:hov?'var(--surface-sunken)':'transparent',color:'var(--text-body)',border:'1px solid var(--border-strong)'},
    ghost:{background:hov?'var(--surface-sunken)':'transparent',color:'var(--gray-600)',border:'1px solid transparent'},
  }[variant];
  return <button aria-label={label} title={label} disabled={disabled} onMouseEnter={()=>setHov(true)} onMouseLeave={()=>setHov(false)}
    style={{display:'inline-flex',alignItems:'center',justifyContent:'center',width:dim,height:dim,borderRadius:'var(--radius-sm)',
    cursor:disabled?'not-allowed':'pointer',opacity:disabled?.45:1,transition:'background var(--dur-fast) var(--ease-out)',...v,...style}} {...rest}>{children}</button>;
}
