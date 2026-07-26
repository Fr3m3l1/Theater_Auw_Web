import React,{useState} from 'react';
export function Select({label,options=[],style,...rest}){
  const [foc,setFoc]=useState(false);
  return <label style={{display:'flex',flexDirection:'column',gap:6,fontFamily:'var(--font-body)',fontSize:14,fontWeight:600,color:'var(--text-body)'}}>
    {label}
    <span style={{position:'relative',display:'block'}}>
    <select onFocus={()=>setFoc(true)} onBlur={()=>setFoc(false)}
      style={{width:'100%',appearance:'none',padding:'10px 34px 10px 12px',fontSize:15,fontWeight:400,fontFamily:'var(--font-body)',color:'var(--text-body)',
      background:'var(--surface-card)',border:`1px solid ${foc?'var(--red-600)':'var(--border-strong)'}`,borderRadius:'var(--radius-sm)',outline:'none',
      boxShadow:foc?'var(--focus-ring)':'none',cursor:'pointer',...style}} {...rest}>
      {options.map(o=><option key={o.value??o} value={o.value??o}>{o.label??o}</option>)}
    </select>
    <svg viewBox="0 0 24 24" width="16" height="16" style={{position:'absolute',right:12,top:'50%',transform:'translateY(-50%)',pointerEvents:'none'}} fill="none" stroke="var(--gray-500)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    </span>
  </label>;
}
